<?php

namespace App\Services;

use App\Exceptions\AiToolRecordNotFoundException;
use App\Exceptions\MissingScopeContextException;
use App\Exceptions\UnauthorizedScopeException;
use App\Models\CenAccount;
use App\Models\Purchase;
use App\Models\User;
use App\Services\Auth\AreaDefinition;
use App\Services\Auth\DataScopeService;
use App\Services\Auth\HorizonScopeContext;
use App\Services\Auth\UserAccessContext;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * AiToolRegistry
 *
 * Safe, read-only tool layer designed for invocation by local AI assistants (e.g. Ollama).
 * Exposes explicit, pre-scoped query functions mirroring the application-wide registry pattern
 * established by DataRevisionService ($tableMap, $allowedDataTables style registries).
 *
 * Every function strictly requires an authenticated acting user and enforces horizon division/unit
 * scoping boundaries, preventing cross-division data leakage in non-web contexts.
 */
class AiToolRegistry
{
    /**
     * Map of available AI tools with metadata and JSON-Schema parameter definitions.
     * Mirrors the registry pattern from DataRevisionService::$tableMap (app/Services/DataRevisionService.php:17-49).
     */
    protected static array $toolMap = [
        'getPurchaseCaseStatus' => [
            'name' => 'getPurchaseCaseStatus',
            'description' => 'Retrieve current status, routing stage, and details of a purchase case within authorized division.',
            'category' => '1A',
            'read_only' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'caseId' => [
                        'type' => 'integer',
                        'description' => 'The purchase case ID (pcs_id).',
                    ],
                ],
                'required' => ['caseId'],
            ],
            'method' => 'getPurchaseCaseStatus',
        ],
        'getChequeDetails' => [
            'name' => 'getChequeDetails',
            'description' => 'Lookup transaction and commitment details by cheque number or reference within authorized scope.',
            'category' => '3A',
            'read_only' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'chequeNumber' => [
                        'type' => 'string',
                        'description' => 'The cheque number, voucher reference, or commitment/transaction ID.',
                    ],
                ],
                'required' => ['chequeNumber'],
            ],
            'method' => 'getChequeDetails',
        ],
        'getAttendanceSummary' => [
            'name' => 'getAttendanceSummary',
            'description' => 'Get monthly attendance summary and day counts for an employee within authorized unit or own attendance.',
            'category' => '5A',
            'read_only' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'employeeId' => [
                        'type' => 'string',
                        'description' => 'The employee ID (e.g. 18-21-08-5273 or numeric ID).',
                    ],
                    'month' => [
                        'type' => 'string',
                        'description' => 'The target month in YYYY-MM format (e.g. 2024-08).',
                    ],
                ],
                'required' => ['employeeId', 'month'],
            ],
            'method' => 'getAttendanceSummary',
        ],
    ];

    /**
     * Allowed tool names whitelist for dynamic dispatching.
     * Mirrors DataRevisionService::$allowedDataTables (app/Services/DataRevisionService.php:54-80).
     */
    protected static array $allowedTools = [
        'getPurchaseCaseStatus',
        'getChequeDetails',
        'getAttendanceSummary',
    ];

    /**
     * Get the full tool registry map.
     */
    public static function getToolMap(): array
    {
        return static::$toolMap;
    }

    /**
     * Get the list of allowed tool names.
     */
    public static function getAllowedTools(): array
    {
        return static::$allowedTools;
    }

    /**
     * Check if a tool is registered.
     */
    public static function hasTool(string $toolName): bool
    {
        return isset(static::$toolMap[$toolName]);
    }

    /**
     * Get the definition of a specific tool.
     */
    public static function getToolDefinition(string $toolName): ?array
    {
        return static::$toolMap[$toolName] ?? null;
    }

    /**
     * Dispatch an AI tool invocation by name with arguments and acting user.
     *
     * @param string $toolName
     * @param array<string, mixed> $arguments
     * @param Authenticatable|CenAccount|User|null $actingUser
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException If tool name is unknown
     * @throws MissingScopeContextException If acting user is missing
     * @throws UnauthorizedScopeException If acting user is unauthorized
     * @throws AiToolRecordNotFoundException If requested record does not exist
     */
    public function invoke(string $toolName, array $arguments, Authenticatable|CenAccount|User|null $actingUser): array
    {
        if (!static::hasTool($toolName)) {
            throw new InvalidArgumentException("AI tool '{$toolName}' is not registered in AiToolRegistry.");
        }

        return match ($toolName) {
            'getPurchaseCaseStatus' => $this->getPurchaseCaseStatus(
                caseId: (int) ($arguments['caseId'] ?? $arguments['case_id'] ?? 0),
                actingUser: $actingUser
            ),
            'getChequeDetails' => $this->getChequeDetails(
                chequeNumber: (string) ($arguments['chequeNumber'] ?? $arguments['cheque_number'] ?? ''),
                actingUser: $actingUser
            ),
            'getAttendanceSummary' => $this->getAttendanceSummary(
                employeeId: (string) ($arguments['employeeId'] ?? $arguments['employee_id'] ?? ''),
                month: (string) ($arguments['month'] ?? now()->format('Y-m')),
                actingUser: $actingUser
            ),
            default => throw new InvalidArgumentException("Handler for tool '{$toolName}' is not mapped."),
        };
    }

    /**
     * Ensure the acting user context is provided and active.
     *
     * @throws MissingScopeContextException
     * @throws UnauthorizedScopeException
     */
    protected function ensureValidScopeContext(Authenticatable|CenAccount|User|null $actingUser): void
    {
        if ($actingUser === null) {
            throw new MissingScopeContextException('An acting user context is required to execute AI tools.');
        }

        $status = strtolower(trim((string) ($actingUser->acc_status ?? 'active')));
        if ($status !== 'active') {
            throw new UnauthorizedScopeException("Acting user account '{$actingUser->acc_username}' is not active.");
        }
    }

    /**
     * Tool 1: getPurchaseCaseStatus
     *
     * Wraps the existing logic behind Purchase::currentSubstatus (app/Models/Purchase.php:422-426),
     * getCurrentStageAttribute (app/Models/Purchase.php:454-457), and
     * getCurrentStageDisplayAttribute (app/Models/Purchase.php:463-466), enforcing Task 1 explicit user scoping.
     *
     * @param int $caseId
     * @param Authenticatable|CenAccount|User|null $actingUser
     * @return array<string, mixed>
     *
     * @throws MissingScopeContextException If acting user is null
     * @throws UnauthorizedScopeException If user lacks authority for the case's unit
     * @throws AiToolRecordNotFoundException If case does not exist
     */
    public function getPurchaseCaseStatus(int $caseId, Authenticatable|CenAccount|User|null $actingUser): array
    {
        $this->ensureValidScopeContext($actingUser);

        // 1. Verify existence globally (bypassing scope only to distinguish 404 from 403)
        /** @var Purchase|null $unscoped */
        $unscoped = Purchase::withoutGlobalScope('horizon')->find($caseId);
        if (!$unscoped) {
            throw new AiToolRecordNotFoundException("Purchase case #{$caseId} does not exist.");
        }

        // 2. Verify authorization for the case unit via DataScopeService
        /** @var DataScopeService $scopeService */
        $scopeService = app(DataScopeService::class);
        $caseUnitId = (int) $unscoped->pcs_unt_id;

        if (!$scopeService->canAccessUnit($actingUser, $caseUnitId)) {
            throw new UnauthorizedScopeException(
                "User '{$actingUser->acc_username}' is not authorized to access purchase case #{$caseId} in unit {$caseUnitId}."
            );
        }

        // 3. Enforce Task 1 explicit scope execution context
        return HorizonScopeContext::runAs($actingUser, function () use ($caseId) {
            // Re-queries through the active horizon scope attached by bootHorizonScoped
            // (app/Traits/HorizonScoped.php:21-48)
            $purchase = Purchase::with(['currentSubstatus'])->find($caseId);

            if (!$purchase) {
                throw new UnauthorizedScopeException("Purchase case #{$caseId} is outside authorized horizon scope.");
            }

            // Return flat, predictable DTO array (reusing accessors in app/Models/Purchase.php:454-466)
            return [
                'case_id' => (int) $purchase->pcs_id,
                'title' => (string) ($purchase->pcs_title ?? ''),
                'status' => (string) ($purchase->pcs_status ?? ''),
                'type' => (string) ($purchase->pcs_type ?? ''),
                'current_stage' => (string) ($purchase->current_stage ?? 'Division'),
                'stage_display' => (string) ($purchase->current_stage_display ?? 'Division (Initiator)'),
                'unit_id' => (int) $purchase->pcs_unt_id,
                'price' => (float) ($purchase->pcs_price ?? 0),
                'substatus_since' => $purchase->currentSubstatus?->pss_since?->toIso8601String(),
            ];
        });
    }

    /**
     * Tool 2: getChequeDetails
     *
     * Wraps the fin.transactions -> fin.commitments join identified in the audit
     * (app/Services/FinancialIntelligenceService.php:176 and app/Http/Controllers/Finance/PaymentController.php:339-350),
     * matching cheque/voucher references stored in fin.commitments.cmt_remarks
     * (as verified in resources/views/finance/payments/show.blade.php:428).
     *
     * @param string $chequeNumber
     * @param Authenticatable|CenAccount|User|null $actingUser
     * @return array<string, mixed>
     *
     * @throws MissingScopeContextException If acting user is null
     * @throws UnauthorizedScopeException If user lacks authority for the record
     * @throws AiToolRecordNotFoundException If cheque/transaction record is not found
     */
    public function getChequeDetails(string $chequeNumber, Authenticatable|CenAccount|User|null $actingUser): array
    {
        $this->ensureValidScopeContext($actingUser);

        $cleanSearch = trim($chequeNumber);
        if ($cleanSearch === '') {
            throw new InvalidArgumentException('Cheque number or reference must not be empty.');
        }

        // Query joining fin.transactions and fin.commitments
        // Legacy link: fin.transactions.trn_cmt_id = fin.commitments.cmt_id
        $query = DB::table('fin.transactions as t')
            ->join('fin.commitments as c', 't.trn_cmt_id', '=', 'c.cmt_id')
            ->leftJoin('cen.heads as eh', 'c.cmt_effhed_id', '=', 'eh.hed_id')
            ->leftJoin('cen.units as eu', 'c.cmt_effunt_id', '=', 'eu.unt_id')
            ->leftJoin('cen.units as cu', 'c.cmt_unt_id', '=', 'cu.unt_id')
            ->where(function ($w) use ($cleanSearch) {
                $w->where('c.cmt_remarks', 'ILIKE', "%{$cleanSearch}%");
                if (is_numeric($cleanSearch)) {
                    $w->orWhere('c.cmt_id', (int) $cleanSearch)
                      ->orWhere('t.trn_id', (int) $cleanSearch)
                      ->orWhere('c.cmt_docid', (int) $cleanSearch);
                }
            })
            ->select(
                't.trn_id',
                't.trn_cmt_id',
                't.trn_date',
                't.trn_amount1',
                't.trn_amount2',
                't.trn_tax1',
                't.trn_balance',
                't.trn_seq',
                'c.cmt_id',
                'c.cmt_docid',
                'c.cmt_type',
                'c.cmt_date',
                'c.cmt_amount',
                'c.cmt_status',
                'c.cmt_unt_id',
                'c.cmt_effunt_id',
                'c.cmt_remarks',
                'eh.hed_code',
                'eh.hed_name',
                'eu.unt_name as eff_unt_name',
                'cu.unt_name as cmt_unt_name'
            );

        $records = $query->orderBy('t.trn_date', 'desc')->get();

        if ($records->isEmpty()) {
            throw new AiToolRecordNotFoundException("No cheque or payment transaction found matching '{$cleanSearch}'.");
        }

        $first = $records->first();
        $recordUnitId = (int) ($first->cmt_effunt_id ?: $first->cmt_unt_id);

        // Check if user is authorized to view this transaction/commitment
        $context = UserAccessContext::forUser($actingUser);
        $userArea = strtolower(trim((string) ($actingUser->acc_untarea ?? '')));
        $isFinOrCommand = $context->isSuperAdmin()
            || $context->isCommand()
            || in_array($userArea, ['fin', 'finance', 'rdw', 'hqs', 'nrdi'], true);

        if (!$isFinOrCommand) {
            /** @var DataScopeService $scopeService */
            $scopeService = app(DataScopeService::class);
            $authorized = $scopeService->canAccessUnit($actingUser, (int) $first->cmt_unt_id)
                || $scopeService->canAccessUnit($actingUser, (int) $first->cmt_effunt_id);

            if (!$authorized) {
                throw new UnauthorizedScopeException(
                    "User '{$actingUser->acc_username}' is not authorized to access payment/cheque record outside their division scope (Unit {$recordUnitId})."
                );
            }
        }

        return HorizonScopeContext::runAs($actingUser, function () use ($cleanSearch, $first, $records, $recordUnitId) {
            return [
                'cheque_number' => $cleanSearch,
                'transaction_id' => (int) $first->trn_id,
                'commitment_id' => (int) $first->cmt_id,
                'document_id' => (int) $first->cmt_docid,
                'transaction_date' => (string) $first->trn_date,
                'commitment_date' => (string) $first->cmt_date,
                'amount' => (float) abs((float) $first->trn_amount2 ?: (float) $first->cmt_amount),
                'net_amount' => (float) abs((float) $first->trn_amount1),
                'tax' => (float) abs((float) $first->trn_tax1),
                'status' => (string) $first->cmt_status,
                'type' => (string) $first->cmt_type,
                'unit_id' => $recordUnitId,
                'unit_name' => (string) ($first->eff_unt_name ?: $first->cmt_unt_name),
                'head_code' => (string) ($first->hed_code ?? ''),
                'head_name' => (string) ($first->hed_name ?? ''),
                'remarks' => (string) ($first->cmt_remarks ?? ''),
                'installments_count' => $records->count(),
            ];
        });
    }

    /**
     * Tool 3: getAttendanceSummary
     *
     * Wraps AttendanceService::getEmpAttendanceSummary (app/Services/AttendanceService.php:714-934),
     * enforcing that non-HR users may only query their own attendance, while HR/Command users
     * or supervisors with division unit access can query authorized employees.
     *
     * @param string|int $employeeId
     * @param string $month YYYY-MM
     * @param Authenticatable|CenAccount|User|null $actingUser
     * @return array<string, mixed>
     *
     * @throws MissingScopeContextException If acting user is null
     * @throws UnauthorizedScopeException If user lacks authority for the employee's attendance
     * @throws AiToolRecordNotFoundException If employee does not exist
     */
    public function getAttendanceSummary(string|int $employeeId, string $month, Authenticatable|CenAccount|User|null $actingUser): array
    {
        $this->ensureValidScopeContext($actingUser);

        $empIdStr = trim((string) $employeeId);
        if ($empIdStr === '') {
            throw new InvalidArgumentException('Employee ID must not be empty.');
        }

        // Global check in hr.emps
        $emp = DB::table('hr.emps')->where('emp_id', $empIdStr)->first();
        if (!$emp) {
            throw new AiToolRecordNotFoundException("Employee '{$empIdStr}' does not exist in hr.emps.");
        }

        // Authorization check: User can query self, or users with HR/Command/Division oversight
        $context = UserAccessContext::forUser($actingUser);
        $userArea = strtolower(trim((string) ($actingUser->acc_untarea ?? '')));
        $userDesig = strtoupper(trim((string) ($actingUser->acc_desig ?? '')));

        /** @var AttendanceService $attendanceService */
        $attendanceService = app(AttendanceService::class);

        $hasHrOversight = $context->isSuperAdmin()
            || $context->isCommand()
            || AreaDefinition::isHr($userArea)
            || str_contains($userDesig, 'HR')
            || $attendanceService->canUserAccessEmployee($actingUser, $empIdStr); // app/Services/AttendanceService.php:50-58

        $isSelf = (string) $actingUser->acc_username === (string) $emp->emp_id
            || (string) ($actingUser->acc_name ?? '') === (string) $emp->emp_name
            || (string) ($actingUser->acc_id ?? '') === $empIdStr;

        if (!$hasHrOversight && !$isSelf) {
            throw new UnauthorizedScopeException(
                "User '{$actingUser->acc_username}' is not authorized to query attendance for employee '{$empIdStr}'."
            );
        }

        // Parse month range
        $dt = Carbon::parse($month . '-01');
        $startDate = $dt->copy()->startOfMonth()->toDateString();
        $endDate = $dt->copy()->endOfMonth()->toDateString();

        return HorizonScopeContext::runAs($actingUser, function () use ($emp, $month, $startDate, $endDate, $attendanceService) {
            // Reuses verified AttendanceService calculation (app/Services/AttendanceService.php:714-934)
            $counts = $attendanceService->getEmpAttendanceSummary((string) $emp->emp_id, $startDate, $endDate);

            return [
                'employee_id' => (string) $emp->emp_id,
                'employee_name' => (string) $emp->emp_name,
                'employee_cnic' => (string) ($emp->emp_cnic ?? ''),
                'unit_id' => (int) $emp->emp_unt_id,
                'month' => (string) $month,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'working_days' => (int) ($counts['working_days'] ?? 0),
                'total_days' => (int) ($counts['total_days'] ?? 0),
                'present' => (int) ($counts['P'] ?? 0),
                'absent' => (int) ($counts['A'] ?? 0),
                'leaves' => (int) (($counts['L'] ?? 0) + ($counts['U'] ?? 0)),
                'weekly_off' => (int) ($counts['W'] ?? 0),
                'holidays' => (int) ($counts['Z'] ?? 0),
                'counts' => $counts,
            ];
        });
    }
}
