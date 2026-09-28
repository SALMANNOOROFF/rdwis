<?php

namespace App\Services;

use App\Exceptions\AiToolRecordNotFoundException;
use App\Exceptions\MissingScopeContextException;
use App\Exceptions\UnauthorizedScopeException;
use App\Models\CenAccount;
use App\Models\Employee;
use App\Models\HrCtrCase;
use App\Models\Project;
use App\Models\Purchase;
use App\Models\User;
use App\Services\Auth\AreaDefinition;
use App\Services\Auth\DataScopeService;
use App\Services\Auth\HorizonScopeContext;
use App\Services\Auth\UserAccessContext;
use App\Services\FinancialIntelligenceService;
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
        'getOrganizationStats' => [
            'name' => 'getOrganizationStats',
            'description' => 'Get overall organization-wide statistics: total employees (active vs released), total projects (active vs completed, total budget), total purchase cases, total contract cases, and divisions summary.',
            'category' => 'ORG',
            'read_only' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'category' => [
                        'type' => 'string',
                        'description' => 'Optional category focus: "all", "employees", "projects", "purchases", "contracts". Default is "all".',
                    ],
                ],
                'required' => [],
            ],
            'method' => 'getOrganizationStats',
        ],
        'getDivisionOverview' => [
            'name' => 'getDivisionOverview',
            'description' => 'Get a comprehensive status overview of the authorized division or unit (active projects, employees, purchase cases, contract cases, and total funding).',
            'category' => 'DIV',
            'read_only' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'divisionId' => [
                        'type' => 'integer',
                        'description' => 'Optional specific division/unit ID or division name (e.g. 200000 or "Communication"). If omitted, defaults to acting user division.',
                    ],
                ],
                'required' => [],
            ],
            'method' => 'getDivisionOverview',
        ],
        'searchProjects' => [
            'name' => 'searchProjects',
            'description' => 'Search and list projects within authorized division/scope by keyword, title, status, division name, or list all divisional projects.',
            'category' => 'PRJ',
            'read_only' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Optional search query (keyword, project title, project code, or division name). Leave empty to list projects.',
                    ],
                    'division' => [
                        'type' => 'string',
                        'description' => 'Optional division name or unit ID (e.g. "Communication", "200000").',
                    ],
                    'status' => [
                        'type' => 'string',
                        'description' => 'Optional project status filter (e.g. Active, Completed, In-Progress).',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum number of projects to return (default 10).',
                    ],
                ],
                'required' => [],
            ],
            'method' => 'searchProjects',
        ],
        'getProjectFinancialDetails' => [
            'name' => 'getProjectFinancialDetails',
            'description' => 'Get complete financial breakdown and fund inflow history (installments, receipts, milestones, dates, allocation, expenditure, and remaining balance) for a project ("paisy kab kia aya").',
            'category' => 'FIN',
            'read_only' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'projectId' => [
                        'type' => 'string',
                        'description' => 'The project ID (numeric prj_id, e.g. 200004) or project code/title (e.g. ELINT).',
                    ],
                ],
                'required' => ['projectId'],
            ],
            'method' => 'getProjectFinancialDetails',
        ],
        'searchPurchaseCases' => [
            'name' => 'searchPurchaseCases',
            'description' => 'Search and list purchase cases within authorized division by title keyword, subject, status, or date.',
            'category' => 'PUR',
            'read_only' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Optional search query (case title, minute, subject, or keyword). Leave empty to list recent cases.',
                    ],
                    'status' => [
                        'type' => 'string',
                        'description' => 'Optional case status filter (e.g. Draft, In Process, Approved, Fulfilled, Cancelled).',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum number of purchase cases to return (default 10).',
                    ],
                ],
                'required' => [],
            ],
            'method' => 'searchPurchaseCases',
        ],
        'getPurchaseCaseDetails' => [
            'name' => 'getPurchaseCaseDetails',
            'description' => 'Get full details of a specific purchase case including items list, quantities, prices, supplier/firm, stage, and approval history.',
            'category' => 'PUR',
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
            'method' => 'getPurchaseCaseDetails',
        ],
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
        'searchContractCases' => [
            'name' => 'searchContractCases',
            'description' => 'Search and list HR contract cases within authorized division by employee name, employee ID, title, or status.',
            'category' => 'CTR',
            'read_only' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Optional search query (employee name, ID, or case title).',
                    ],
                    'status' => [
                        'type' => 'string',
                        'description' => 'Optional contract case status (e.g. Draft, In Process, Approved, Fulfilled).',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum number of contract cases to return (default 10).',
                    ],
                ],
                'required' => [],
            ],
            'method' => 'searchContractCases',
        ],
        'getContractCaseDetails' => [
            'name' => 'getContractCaseDetails',
            'description' => 'Get full details of a specific HR contract case including proposed/approved salary, dates, current stage, and employee information.',
            'category' => 'CTR',
            'read_only' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'caseId' => [
                        'type' => 'integer',
                        'description' => 'The contract case ID (ctc_id).',
                    ],
                ],
                'required' => ['caseId'],
            ],
            'method' => 'getContractCaseDetails',
        ],
        'searchEmployees' => [
            'name' => 'searchEmployees',
            'description' => 'Search and list employees within authorized division/scope by name, ID, CNIC, rank, or designation.',
            'category' => 'HR',
            'read_only' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Optional search query (employee name, ID, CNIC, rank, or title).',
                    ],
                    'status' => [
                        'type' => 'string',
                        'description' => 'Optional employee status (e.g. Active, Released).',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum number of employees to return (default 10).',
                    ],
                ],
                'required' => [],
            ],
            'method' => 'searchEmployees',
        ],
        'getEmployeeDetails' => [
            'name' => 'getEmployeeDetails',
            'description' => 'Get full profile details for an employee including designation, rank, division, joining date, contract history, and leave summary.',
            'category' => 'HR',
            'read_only' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'employeeId' => [
                        'type' => 'string',
                        'description' => 'The employee ID (e.g. 14-20-11-8023) or CNIC.',
                    ],
                ],
                'required' => ['employeeId'],
            ],
            'method' => 'getEmployeeDetails',
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
                        'description' => 'The target month in YYYY-MM format (e.g. 2024-08). Defaults to current month if omitted.',
                    ],
                ],
                'required' => ['employeeId'],
            ],
            'method' => 'getAttendanceSummary',
        ],
    ];

    /**
     * Allowed tool names whitelist for dynamic dispatching.
     * Mirrors DataRevisionService::$allowedDataTables (app/Services/DataRevisionService.php:54-80).
     */
    protected static array $allowedTools = [
        'getOrganizationStats',
        'getDivisionOverview',
        'searchProjects',
        'getProjectFinancialDetails',
        'searchPurchaseCases',
        'getPurchaseCaseDetails',
        'getPurchaseCaseStatus',
        'searchContractCases',
        'getContractCaseDetails',
        'searchEmployees',
        'getEmployeeDetails',
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

        $arg = function (array $keys, $default = null) use ($arguments) {
            foreach ($keys as $k) {
                if (array_key_exists($k, $arguments) && $arguments[$k] !== null && $arguments[$k] !== '') {
                    return $arguments[$k];
                }
            }
            return $default;
        };

        return match ($toolName) {
            'getOrganizationStats' => $this->getOrganizationStats(
                category: (string) ($arg(['category', 'type', 'focus', 'query'], 'all')),
                actingUser: $actingUser
            ),
            'getDivisionOverview' => $this->getDivisionOverview(
                divisionId: ($divId = $arg(['divisionId', 'division_id', 'unitId', 'unit_id', 'division', 'divisionName', 'division_name'])) !== null
                    ? (is_numeric($divId) ? (int) $divId : $this->resolveDivisionIdByName((string) $divId))
                    : null,
                actingUser: $actingUser
            ),
            'searchProjects' => $this->searchProjects(
                query: $arg(['query', 'searchTerm', 'keyword', 'search', 'title', 'code']),
                status: $arg(['status']),
                limit: (int) ($arg(['limit'], 10)),
                actingUser: $actingUser,
                division: $arg(['division', 'divisionName', 'division_name', 'unit', 'unit_name'])
            ),
            'getProjectFinancialDetails' => $this->getProjectFinancialDetails(
                projectId: $arg(['projectId', 'project_id', 'id', 'query', 'code'], 0),
                actingUser: $actingUser
            ),
            'searchPurchaseCases' => $this->searchPurchaseCases(
                query: $arg(['query', 'searchTerm', 'keyword', 'search', 'title', 'subject']),
                status: $arg(['status']),
                limit: (int) ($arg(['limit'], 10)),
                actingUser: $actingUser
            ),
            'getPurchaseCaseDetails' => $this->getPurchaseCaseDetails(
                caseId: (int) ($arg(['caseId', 'case_id', 'id'], 0)),
                actingUser: $actingUser
            ),
            'getPurchaseCaseStatus' => $this->getPurchaseCaseStatus(
                caseId: (int) ($arg(['caseId', 'case_id', 'id'], 0)),
                actingUser: $actingUser
            ),
            'searchContractCases' => $this->searchContractCases(
                query: $arg(['query', 'searchTerm', 'keyword', 'search', 'employeeName', 'employee_name']),
                status: $arg(['status']),
                limit: (int) ($arg(['limit'], 10)),
                actingUser: $actingUser
            ),
            'getContractCaseDetails' => $this->getContractCaseDetails(
                caseId: (int) ($arg(['caseId', 'case_id', 'id'], 0)),
                actingUser: $actingUser
            ),
            'searchEmployees' => $this->searchEmployees(
                query: $arg(['query', 'searchTerm', 'keyword', 'search', 'name', 'employeeName', 'employee_name']),
                status: $arg(['status']),
                limit: (int) ($arg(['limit'], 10)),
                actingUser: $actingUser
            ),
            'getEmployeeDetails' => $this->getEmployeeDetails(
                employeeId: (string) ($arg(['employeeId', 'employee_id', 'empId', 'emp_id', 'id', 'cnic'], '')),
                actingUser: $actingUser
            ),
            'getChequeDetails' => $this->getChequeDetails(
                chequeNumber: (string) ($arg(['chequeNumber', 'cheque_number', 'cheque', 'reference', 'ref'], '')),
                actingUser: $actingUser
            ),
            'getAttendanceSummary' => $this->getAttendanceSummary(
                employeeId: (string) ($arg(['employeeId', 'employee_id', 'empId', 'emp_id', 'id'], '')),
                month: (string) ($arg(['month', 'date'], now()->format('Y-m'))),
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

    /**
     * Resolve a division/unit ID by human-readable division name.
     */
    protected function resolveDivisionIdByName(string $name): ?int
    {
        $clean = trim(preg_replace('/\b(division|div)\b/i', '', $name));
        if ($clean === '') {
            $clean = trim($name);
        }
        $unit = DB::table('cen.units')
            ->where('unt_name', 'ILIKE', "%{$clean}%")
            ->first();
        return $unit ? (int) $unit->unt_id : null;
    }

    /**
     * Tool: getOrganizationStats
     *
     * Provides high-level organization-wide summary statistics (total employees active/released,
     * total projects, approved budgets, total purchases, and contract cases).
     * Fast (< 1 second) aggregation query for high-level management and central wings.
     */
    public function getOrganizationStats(?string $category, Authenticatable|CenAccount|User|null $actingUser): array
    {
        $this->ensureValidScopeContext($actingUser);

        $context = UserAccessContext::forUser($actingUser);
        $userArea = strtolower(trim((string) ($actingUser->acc_untarea ?? '')));
        $isWide = $context->isSuperAdmin()
            || $context->isCommand()
            || in_array($userArea, ['fin', 'finance', 'hr', 'proc', 'hqs', 'it', 'adm', 'rdw', 'nrdi'], true);

        if (!$isWide) {
            $divStats = $this->getDivisionOverview(null, $actingUser);
            return [
                'scope' => 'division_only',
                'notice' => 'You have division-level access. Organization-wide statistics are restricted to Central/Command wings. Showing your division stats:',
                'division_stats' => $divStats,
            ];
        }

        return HorizonScopeContext::runAs($actingUser, function () use ($category) {
            $cat = strtolower(trim((string) $category));

            $stats = [
                'scope' => 'organization_wide',
                'organization_name' => 'RDW / NRDI Organization',
            ];

            // Employees stats
            if ($cat === '' || $cat === 'all' || str_contains($cat, 'emp') || str_contains($cat, 'hr')) {
                $totalEmployees = DB::table('hr.emps')->count();
                $activeEmployees = DB::table('hr.emps')->where('emp_status', 'Active')->count();
                $releasedEmployees = DB::table('hr.emps')->where('emp_status', 'Released')->count();

                $stats['employees'] = [
                    'total_employees' => $totalEmployees,
                    'active_employees' => $activeEmployees,
                    'released_employees' => $releasedEmployees,
                    'other_or_on_leave' => $totalEmployees - ($activeEmployees + $releasedEmployees),
                ];
            }

            // Projects stats
            if ($cat === '' || $cat === 'all' || str_contains($cat, 'prj') || str_contains($cat, 'proj')) {
                $totalProjects = DB::table('prj.projects')->count();
                $activeProjects = DB::table('prj.projects')
                    ->where(function ($w) {
                        $w->whereNull('prj_status')
                          ->orWhereNotIn('prj_status', ['Completed', 'Terminated', 'Closed']);
                    })->count();
                $completedProjects = DB::table('prj.projects')
                    ->whereIn('prj_status', ['Completed', 'Closed'])->count();
                $totalBudget = (float) DB::table('prj.projects')
                    ->sum(DB::raw('COALESCE(prj_aprvcost, prj_propcost, 0)'));

                $stats['projects'] = [
                    'total_projects' => $totalProjects,
                    'active_projects' => $activeProjects,
                    'completed_projects' => $completedProjects,
                    'total_allocated_budget' => $totalBudget,
                ];
            }

            // Procurement & Contract stats
            if ($cat === '' || $cat === 'all' || str_contains($cat, 'pur') || str_contains($cat, 'ctr') || str_contains($cat, 'case')) {
                $totalPurchases = DB::table('pur.purcases')->count();
                $openPurchases = DB::table('pur.purcases')
                    ->whereNotIn('pcs_status', ['Fulfilled', 'Cancelled', 'Rejected'])->count();
                $totalContracts = DB::table('hr.ctrcases')->count();
                $openContracts = DB::table('hr.ctrcases')
                    ->whereNotIn('ctc_status', ['Approved', 'Fulfilled', 'Cancelled'])->count();

                $stats['procurement_and_contracts'] = [
                    'total_purchase_cases' => $totalPurchases,
                    'open_purchase_cases' => $openPurchases,
                    'total_contract_cases' => $totalContracts,
                    'open_contract_cases' => $openContracts,
                ];
            }

            // Divisions count
            if ($cat === '' || $cat === 'all') {
                $stats['divisions_count'] = DB::table('cen.units')
                    ->where('unt_id', '>=', 200000)
                    ->where('unt_id', '<', 900000)
                    ->count();
            }

            return $stats;
        });
    }

    /**
     * Tool 4: getDivisionOverview
     *
     * Provides a high-level summary overview of the authorized division or unit:
     * active projects, total employees, purchase cases, contract cases, and total funding.
     */
    public function getDivisionOverview(?int $divisionId, Authenticatable|CenAccount|User|null $actingUser): array
    {
        $this->ensureValidScopeContext($actingUser);

        $userUnitId = (int) ($actingUser->acc_unt_id ?? 0);
        $targetUnitId = $userUnitId;

        if ($divisionId !== null && $divisionId > 0) {
            /** @var DataScopeService $scopeService */
            $scopeService = app(DataScopeService::class);
            if (!$scopeService->canAccessUnit($actingUser, $divisionId)) {
                throw new UnauthorizedScopeException(
                    "User '{$actingUser->acc_username}' is not authorized to access Division (Unit {$divisionId})."
                );
            }
            $targetUnitId = $divisionId;
        }

        $unit = DB::table('cen.units')->where('unt_id', $targetUnitId)->first();
        $unitName = $unit->unt_name ?? "Division #{$targetUnitId}";

        return HorizonScopeContext::runAs($actingUser, function () use ($targetUnitId, $unitName) {
            $totalProjects = DB::table('prj.projects')->where('prj_unt_id', $targetUnitId)->count();
            $activeProjects = DB::table('prj.projects')
                ->where('prj_unt_id', $targetUnitId)
                ->where(function ($w) {
                    $w->whereNull('prj_status')
                      ->orWhereNotIn('prj_status', ['Completed', 'Terminated', 'Closed']);
                })
                ->count();

            $totalEmployees = DB::table('hr.emps')->where('emp_unt_id', $targetUnitId)->count();
            $activeEmployees = DB::table('hr.emps')
                ->where('emp_unt_id', $targetUnitId)
                ->where('emp_status', 'Active')
                ->count();

            $totalPurchases = DB::table('pur.purcases')->where('pcs_unt_id', $targetUnitId)->count();
            $openPurchases = DB::table('pur.purcases')
                ->where('pcs_unt_id', $targetUnitId)
                ->whereNotIn('pcs_status', ['Fulfilled', 'Cancelled', 'Rejected'])
                ->count();

            $totalContracts = DB::table('hr.ctrcases')
                ->where(function ($q) use ($targetUnitId) {
                    $q->where('ctc_unt_id', $targetUnitId)
                      ->orWhere('ctc_divisionid', $targetUnitId);
                })
                ->count();

            $totalProjectBudget = (float) DB::table('prj.projects')
                ->where('prj_unt_id', $targetUnitId)
                ->sum(DB::raw('COALESCE(prj_aprvcost, prj_propcost, 0)'));

            return [
                'division_id' => $targetUnitId,
                'division_name' => $unitName,
                'total_projects' => $totalProjects,
                'active_projects' => $activeProjects,
                'total_employees' => $totalEmployees,
                'active_employees' => $activeEmployees,
                'total_purchase_cases' => $totalPurchases,
                'open_purchase_cases' => $openPurchases,
                'total_contract_cases' => $totalContracts,
                'total_project_budget' => $totalProjectBudget,
            ];
        });
    }

    /**
     * Tool 5: searchProjects
     *
     * Searches and lists projects within the user's authorized scope.
     */
    public function searchProjects(?string $query, ?string $status, int $limit, Authenticatable|CenAccount|User|null $actingUser, ?string $division = null): array
    {
        $this->ensureValidScopeContext($actingUser);

        $cleanQuery = trim((string) $query);
        $cleanStatus = trim((string) $status);
        $cleanDivision = trim((string) $division);
        $limit = min(30, max(1, $limit ?: 10));

        return HorizonScopeContext::runAs($actingUser, function () use ($cleanQuery, $cleanStatus, $cleanDivision, $limit, $actingUser) {
            $builder = DB::table('prj.projects as p')
                ->leftJoin('cen.units as u', 'p.prj_unt_id', '=', 'u.unt_id');

            /** @var DataScopeService $scopeService */
            $scopeService = app(DataScopeService::class);
            $scopeService->applyScope($builder, $actingUser, 'p.prj_unt_id');

            if ($cleanDivision !== '') {
                $builder->where(function ($w) use ($cleanDivision) {
                    $w->where('u.unt_name', 'ILIKE', "%{$cleanDivision}%");
                    if (is_numeric($cleanDivision)) {
                        $w->orWhere('p.prj_unt_id', (int) $cleanDivision);
                    }
                });
            }

            if ($cleanQuery !== '') {
                $builder->where(function ($w) use ($cleanQuery) {
                    $w->where('p.prj_title', 'ILIKE', "%{$cleanQuery}%")
                      ->orWhere('p.prj_code', 'ILIKE', "%{$cleanQuery}%")
                      ->orWhere('p.prj_scope', 'ILIKE', "%{$cleanQuery}%")
                      ->orWhere('u.unt_name', 'ILIKE', "%{$cleanQuery}%");

                    $simplified = trim(preg_replace('/\b(division|div)\b/i', '', $cleanQuery));
                    if ($simplified !== '' && strtolower($simplified) !== strtolower($cleanQuery)) {
                        $w->orWhere('u.unt_name', 'ILIKE', "%{$simplified}%");
                    }

                    if (is_numeric($cleanQuery)) {
                        $w->orWhere('p.prj_id', (int) $cleanQuery);
                    }
                });
            }

            if ($cleanStatus !== '') {
                $builder->where('p.prj_status', 'ILIKE', "%{$cleanStatus}%");
            }

            $projects = $builder->select(
                'p.prj_id',
                'p.prj_code',
                'p.prj_title',
                'p.prj_status',
                'p.prj_propcost',
                'p.prj_aprvcost',
                'p.prj_startdt',
                'p.prj_enddt',
                'p.prj_sponsor',
                'p.prj_unt_id',
                'u.unt_name as division_name'
            )->orderBy('p.prj_id', 'desc')->limit($limit)->get();

            return [
                'count' => $projects->count(),
                'projects' => $projects->map(fn($p) => [
                    'project_id' => (int) $p->prj_id,
                    'code' => (string) ($p->prj_code ?? ''),
                    'title' => (string) ($p->prj_title ?? ''),
                    'status' => (string) ($p->prj_status ?? 'Initiated'),
                    'cost' => (float) ($p->prj_aprvcost ?: $p->prj_propcost ?: 0),
                    'start_date' => (string) ($p->prj_startdt ?? ''),
                    'end_date' => (string) ($p->prj_enddt ?? ''),
                    'sponsor' => (string) ($p->prj_sponsor ?? ''),
                    'division_id' => (int) $p->prj_unt_id,
                    'division_name' => (string) ($p->division_name ?? ''),
                ])->all(),
            ];
        });
    }

    /**
     * Tool 6: getProjectFinancialDetails
     *
     * Provides complete financial breakdown and fund inflow history (installments, receipts,
     * milestones, dates, allocation, expenditure, and remaining balance) for a project ("paisy kab kia aya").
     */
    public function getProjectFinancialDetails(string|int $projectId, Authenticatable|CenAccount|User|null $actingUser): array
    {
        $this->ensureValidScopeContext($actingUser);

        $cleanSearch = trim((string) $projectId);
        if ($cleanSearch === '') {
            throw new InvalidArgumentException('Project ID, code, or title must not be empty.');
        }

        // Find project globally first to verify unit and distinguish 404 from 403
        $projectQuery = Project::withoutGlobalScope('horizon');
        if (is_numeric($cleanSearch)) {
            $project = $projectQuery->where('prj_id', (int) $cleanSearch)->first();
        } else {
            $project = $projectQuery->where(function ($w) use ($cleanSearch) {
                $w->where('prj_code', 'ILIKE', "%{$cleanSearch}%")
                  ->orWhere('prj_title', 'ILIKE', "%{$cleanSearch}%");
            })->first();
        }

        if (!$project) {
            $headRec = DB::table('cen.heads')->where(function ($w) use ($cleanSearch) {
                if (is_numeric($cleanSearch)) {
                    $w->where('hed_id', (int) $cleanSearch)->orWhere('hed_prj_id', (int) $cleanSearch);
                } else {
                    $w->where('hed_code', 'ILIKE', "%{$cleanSearch}%")->orWhere('hed_name', 'ILIKE', "%{$cleanSearch}%");
                }
            })->first();

            if ($headRec && $headRec->hed_prj_id) {
                $project = Project::withoutGlobalScope('horizon')->where('prj_id', $headRec->hed_prj_id)->first();
            }
        }

        if (!$project) {
            throw new AiToolRecordNotFoundException("Project matching '{$cleanSearch}' does not exist.");
        }

        /** @var DataScopeService $scopeService */
        $scopeService = app(DataScopeService::class);
        $projectUnitId = (int) $project->prj_unt_id;
        if (!$scopeService->canAccessUnit($actingUser, $projectUnitId)) {
            throw new UnauthorizedScopeException(
                "User '{$actingUser->acc_username}' is not authorized to access Project #{$project->prj_id} under division unit {$projectUnitId}."
            );
        }

        return HorizonScopeContext::runAs($actingUser, function () use ($project) {
            $unit = DB::table('cen.units')->where('unt_id', $project->prj_unt_id)->first();
            $divisionName = $unit->unt_name ?? 'Unknown Division';

            // Find linked head record
            $headRecord = DB::table('cen.heads')
                ->where('hed_prj_id', $project->prj_id)
                ->orWhere('hed_id', $project->prj_id)
                ->first();

            $headStatus = null;
            $installments = collect();
            if ($headRecord) {
                /** @var FinancialIntelligenceService $finService */
                $finService = app(FinancialIntelligenceService::class);
                $headStatus = $finService->getHeadStatus($headRecord->hed_id);

                // "paisy kab kia aya": installments received from fin.sharesinstall + fin.transactions + prj.milestones
                $installments = DB::table('fin.sharesinstall as si')
                    ->leftJoin('fin.transactions as t', 't.trn_id', '=', 'si.shi_fitrn_id')
                    ->leftJoin('prj.milestones as m', 'm.msn_idd', '=', 'si.shi_msn_idd')
                    ->where('si.shi_hed_id', $headRecord->hed_id)
                    ->select(
                        'si.shi_id',
                        't.trn_date',
                        'si.shi_prj',
                        'si.shi_pcc',
                        'si.shi_cf',
                        'm.msn_desc'
                    )
                    ->orderBy('t.trn_date', 'asc')
                    ->get();
            }

            // Milestones summary
            $milestones = DB::table('prj.milestones')
                ->where('msn_xprj_id', $project->prj_id)
                ->select('msn_id', 'msn_desc', 'msn_status', 'msn_cost', 'msn_targetdt', 'msn_achvdt')
                ->orderBy('msn_id', 'asc')
                ->get();

            $headArr = (array) ($headStatus ?? []);
            $cost = (float) ($project->prj_aprvcost ?: $project->prj_propcost ?: 0);
            $allocation = (float) ($headArr['allocation'] ?? $cost);
            $received = (float) ($headArr['received'] ?? 0);
            $expenditure = (float) ($headArr['acc_expenditure'] ?? 0);
            $remaining = $received - $expenditure;

            return [
                'project_id' => (int) $project->prj_id,
                'project_code' => (string) ($project->prj_code ?? ''),
                'project_title' => (string) ($project->prj_title ?? ''),
                'project_status' => (string) ($project->prj_status ?? ''),
                'division_id' => (int) $project->prj_unt_id,
                'division_name' => $divisionName,
                'project_cost' => $cost,
                'financial_overview' => [
                    'total_allocation' => $allocation,
                    'total_received' => $received,
                    'pcc_received' => (float) ($headArr['pcc_received'] ?? 0),
                    'cf_received' => (float) ($headArr['cf_received'] ?? 0),
                    'total_expenditure' => $expenditure,
                    'remaining_balance' => round($remaining, 2),
                    'mtss_share' => (float) ($headArr['mtss_share'] ?? 0),
                    'rdw_share' => (float) ($headArr['rdw_share'] ?? 0),
                ],
                'funds_history_kab_kia_aya' => $installments->map(fn($ins) => [
                    'installment_id' => (int) $ins->shi_id,
                    'date' => (string) ($ins->trn_date ?? 'N/A'),
                    'amount_received' => (float) ($ins->shi_prj ?: ((float) $ins->shi_pcc + (float) $ins->shi_cf)),
                    'pcc_amount' => (float) ($ins->shi_pcc ?? 0),
                    'cf_amount' => (float) ($ins->shi_cf ?? 0),
                    'milestone' => (string) ($ins->msn_desc ?? 'Fund Inflow'),
                ])->all(),
                'milestones' => $milestones->map(fn($m) => [
                    'description' => (string) ($m->msn_desc ?? ''),
                    'status' => (string) ($m->msn_status ?? ''),
                    'cost' => (float) ($m->msn_cost ?? 0),
                    'target_date' => (string) ($m->msn_targetdt ?? ''),
                    'achieved_date' => (string) ($m->msn_achvdt ?? ''),
                ])->all(),
            ];
        });
    }

    /**
     * Tool 7: searchPurchaseCases
     *
     * Searches and lists purchase cases within authorized division.
     */
    public function searchPurchaseCases(?string $query, ?string $status, int $limit, Authenticatable|CenAccount|User|null $actingUser): array
    {
        $this->ensureValidScopeContext($actingUser);

        $cleanQuery = trim((string) $query);
        $cleanStatus = trim((string) $status);
        $limit = min(30, max(1, $limit ?: 10));

        return HorizonScopeContext::runAs($actingUser, function () use ($cleanQuery, $cleanStatus, $limit, $actingUser) {
            $builder = DB::table('pur.purcases as p')
                ->leftJoin('cen.units as u', 'p.pcs_unt_id', '=', 'u.unt_id');

            /** @var DataScopeService $scopeService */
            $scopeService = app(DataScopeService::class);
            $scopeService->applyScope($builder, $actingUser, 'p.pcs_unt_id');

            if ($cleanQuery !== '') {
                $builder->where(function ($w) use ($cleanQuery) {
                    $w->where('p.pcs_title', 'ILIKE', "%{$cleanQuery}%")
                      ->orWhere('p.pcs_remarks', 'ILIKE', "%{$cleanQuery}%")
                      ->orWhere('p.pcs_minute', 'ILIKE', "%{$cleanQuery}%")
                      ->orWhere('u.unt_name', 'ILIKE', "%{$cleanQuery}%");

                    $simplified = trim(preg_replace('/\b(division|div)\b/i', '', $cleanQuery));
                    if ($simplified !== '' && strtolower($simplified) !== strtolower($cleanQuery)) {
                        $w->orWhere('u.unt_name', 'ILIKE', "%{$simplified}%");
                    }

                    if (is_numeric($cleanQuery)) {
                        $w->orWhere('p.pcs_id', (int) $cleanQuery);
                    }
                });
            }

            if ($cleanStatus !== '') {
                $builder->where('p.pcs_status', 'ILIKE', "%{$cleanStatus}%");
            }

            $cases = $builder->select(
                'p.pcs_id',
                'p.pcs_title',
                'p.pcs_status',
                'p.pcs_type',
                'p.pcs_price',
                'p.pcs_date',
                'p.pcs_unt_id',
                'u.unt_name as division_name'
            )->orderBy('p.pcs_id', 'desc')->limit($limit)->get();

            return [
                'count' => $cases->count(),
                'purchase_cases' => $cases->map(fn($c) => [
                    'case_id' => (int) $c->pcs_id,
                    'title' => (string) ($c->pcs_title ?? ''),
                    'status' => (string) ($c->pcs_status ?? ''),
                    'type' => (string) ($c->pcs_type ?? ''),
                    'price' => (float) ($c->pcs_price ?? 0),
                    'date' => (string) ($c->pcs_date ?? ''),
                    'division_id' => (int) $c->pcs_unt_id,
                    'division_name' => (string) ($c->division_name ?? ''),
                ])->all(),
            ];
        });
    }

    /**
     * Tool 8: getPurchaseCaseDetails
     *
     * Retrieves full details of a specific purchase case including items list, quantities,
     * prices, supplier/firm, stage, and approval history.
     */
    public function getPurchaseCaseDetails(int $caseId, Authenticatable|CenAccount|User|null $actingUser): array
    {
        $this->ensureValidScopeContext($actingUser);

        /** @var Purchase|null $unscoped */
        $unscoped = Purchase::withoutGlobalScope('horizon')->with(['firm', 'currentSubstatus'])->find($caseId);
        if (!$unscoped) {
            throw new AiToolRecordNotFoundException("Purchase case #{$caseId} does not exist.");
        }

        /** @var DataScopeService $scopeService */
        $scopeService = app(DataScopeService::class);
        $caseUnitId = (int) $unscoped->pcs_unt_id;
        if (!$scopeService->canAccessUnit($actingUser, $caseUnitId)) {
            throw new UnauthorizedScopeException(
                "User '{$actingUser->acc_username}' is not authorized to access purchase case #{$caseId} in unit {$caseUnitId}."
            );
        }

        return HorizonScopeContext::runAs($actingUser, function () use ($caseId) {
            $purchase = Purchase::with(['firm', 'currentSubstatus'])->find($caseId);
            if (!$purchase) {
                throw new UnauthorizedScopeException("Purchase case #{$caseId} is outside authorized horizon scope.");
            }

            $items = DB::table('pur.purcaseitems')
                ->where('pci_pcs_id', $caseId)
                ->select('pci_desc', 'pci_qty', 'pci_qtyunit', 'pci_price', 'pci_estprice', 'pci_subhead')
                ->get();

            $unit = DB::table('cen.units')->where('unt_id', $purchase->pcs_unt_id)->first();

            return [
                'case_id' => (int) $purchase->pcs_id,
                'title' => (string) ($purchase->pcs_title ?? ''),
                'remarks' => (string) ($purchase->pcs_remarks ?? ''),
                'minute_number' => (string) ($purchase->pcs_minute ?? ''),
                'status' => (string) ($purchase->pcs_status ?? ''),
                'type' => (string) ($purchase->pcs_type ?? ''),
                'current_stage' => (string) ($purchase->current_stage ?? 'Division'),
                'stage_display' => (string) ($purchase->current_stage_display ?? 'Division (Initiator)'),
                'price' => (float) ($purchase->pcs_price ?? 0),
                'date' => (string) ($purchase->pcs_date ?? ''),
                'division_id' => (int) $purchase->pcs_unt_id,
                'division_name' => (string) ($unit->unt_name ?? ''),
                'vendor_name' => (string) ($purchase->firm?->frm_name ?? 'N/A'),
                'items_count' => $items->count(),
                'items' => $items->map(fn($it) => [
                    'description' => (string) ($it->pci_desc ?? ''),
                    'quantity' => (float) ($it->pci_qty ?? 0),
                    'unit' => (string) ($it->pci_qtyunit ?? ''),
                    'price' => (float) ($it->pci_price ?: $it->pci_estprice ?: 0),
                    'subhead' => (string) ($it->pci_subhead ?? ''),
                ])->all(),
            ];
        });
    }

    /**
     * Tool 9: searchContractCases
     *
     * Searches and lists HR contract cases within authorized division.
     */
    public function searchContractCases(?string $query, ?string $status, int $limit, Authenticatable|CenAccount|User|null $actingUser): array
    {
        $this->ensureValidScopeContext($actingUser);

        $cleanQuery = trim((string) $query);
        $cleanStatus = trim((string) $status);
        $limit = min(30, max(1, $limit ?: 10));

        return HorizonScopeContext::runAs($actingUser, function () use ($cleanQuery, $cleanStatus, $limit, $actingUser) {
            $builder = DB::table('hr.ctrcases as c')
                ->leftJoin('cen.units as u', 'c.ctc_unt_id', '=', 'u.unt_id');

            /** @var DataScopeService $scopeService */
            $scopeService = app(DataScopeService::class);
            $scopeService->applyScope($builder, $actingUser, 'c.ctc_unt_id');

            if ($cleanQuery !== '') {
                $builder->where(function ($w) use ($cleanQuery) {
                    $w->where('c.ctc_empnamecomp', 'ILIKE', "%{$cleanQuery}%")
                      ->orWhere('c.ctc_emp_id', 'ILIKE', "%{$cleanQuery}%")
                      ->orWhere('c.ctc_newjobtitle', 'ILIKE', "%{$cleanQuery}%")
                      ->orWhere('u.unt_name', 'ILIKE', "%{$cleanQuery}%");

                    $simplified = trim(preg_replace('/\b(division|div)\b/i', '', $cleanQuery));
                    if ($simplified !== '' && strtolower($simplified) !== strtolower($cleanQuery)) {
                        $w->orWhere('u.unt_name', 'ILIKE', "%{$simplified}%");
                    }

                    if (is_numeric($cleanQuery)) {
                        $w->orWhere('c.ctc_id', (int) $cleanQuery);
                    }
                });
            }

            if ($cleanStatus !== '') {
                $builder->where('c.ctc_status', 'ILIKE', "%{$cleanStatus}%");
            }

            $cases = $builder->select(
                'c.ctc_id',
                'c.ctc_emp_id',
                'c.ctc_empnamecomp',
                'c.ctc_newjobtitle',
                'c.ctc_status',
                'c.ctc_newsalary',
                'c.ctc_approvedsalary',
                'c.ctc_newstartdt',
                'c.ctc_newenddt',
                'c.ctc_unt_id',
                'u.unt_name as division_name'
            )->orderBy('c.ctc_id', 'desc')->limit($limit)->get();

            return [
                'count' => $cases->count(),
                'contract_cases' => $cases->map(fn($c) => [
                    'case_id' => (int) $c->ctc_id,
                    'employee_id' => (string) ($c->ctc_emp_id ?? ''),
                    'employee_name' => (string) ($c->ctc_empnamecomp ?? ''),
                    'job_title' => (string) ($c->ctc_newjobtitle ?? ''),
                    'status' => (string) ($c->ctc_status ?? ''),
                    'proposed_salary' => (float) ($c->ctc_newsalary ?? 0),
                    'approved_salary' => (float) ($c->ctc_approvedsalary ?? 0),
                    'start_date' => (string) ($c->ctc_newstartdt ?? ''),
                    'end_date' => (string) ($c->ctc_newenddt ?? ''),
                    'division_id' => (int) $c->ctc_unt_id,
                    'division_name' => (string) ($c->division_name ?? ''),
                ])->all(),
            ];
        });
    }

    /**
     * Tool 10: getContractCaseDetails
     *
     * Retrieves full details of a specific HR contract case.
     */
    public function getContractCaseDetails(int $caseId, Authenticatable|CenAccount|User|null $actingUser): array
    {
        $this->ensureValidScopeContext($actingUser);

        $case = HrCtrCase::find($caseId);
        if (!$case) {
            throw new AiToolRecordNotFoundException("Contract case #{$caseId} does not exist.");
        }

        $caseUnitId = (int) ($case->ctc_divisionid ?: $case->ctc_unt_id);
        /** @var DataScopeService $scopeService */
        $scopeService = app(DataScopeService::class);
        if (!$scopeService->canAccessUnit($actingUser, $caseUnitId)) {
            throw new UnauthorizedScopeException(
                "User '{$actingUser->acc_username}' is not authorized to access contract case #{$caseId} under division {$caseUnitId}."
            );
        }

        return HorizonScopeContext::runAs($actingUser, function () use ($case, $caseUnitId) {
            $unit = DB::table('cen.units')->where('unt_id', $caseUnitId)->first();

            return [
                'case_id' => (int) $case->ctc_id,
                'employee_id' => (string) ($case->ctc_emp_id ?? ''),
                'employee_name' => (string) ($case->ctc_empnamecomp ?? ''),
                'status' => (string) ($case->ctc_status ?? ''),
                'current_stage' => (string) ($case->ctc_currentstage ?? 'Division'),
                'stage_display' => (string) ($case->current_stage_display ?? 'Division Initiator'),
                'job_title' => (string) ($case->ctc_approvedjobtitle ?: $case->ctc_newjobtitle ?: ''),
                'grade' => (string) ($case->ctc_approvedgrade ?: $case->ctc_newgrade ?: ''),
                'salary' => (float) ($case->ctc_approvedsalary ?: $case->ctc_newsalary ?: 0),
                'proposed_salary' => (float) ($case->ctc_newsalary ?? 0),
                'start_date' => (string) ($case->ctc_approvedstartdt ?: $case->ctc_newstartdt ?: ''),
                'end_date' => (string) ($case->ctc_approvedenddt ?: $case->ctc_newenddt ?: ''),
                'division_id' => $caseUnitId,
                'division_name' => (string) ($unit->unt_name ?? ''),
                'remarks' => (string) ($case->ctc_remarks ?? ''),
            ];
        });
    }

    /**
     * Tool 11: searchEmployees
     *
     * Searches and lists employees within authorized division/scope.
     */
    public function searchEmployees(?string $query, ?string $status, int $limit, Authenticatable|CenAccount|User|null $actingUser): array
    {
        $this->ensureValidScopeContext($actingUser);

        $cleanQuery = trim((string) $query);
        $cleanStatus = trim((string) $status);
        $limit = min(30, max(1, $limit ?: 10));

        return HorizonScopeContext::runAs($actingUser, function () use ($cleanQuery, $cleanStatus, $limit, $actingUser) {
            $builder = DB::table('hr.emps as e')
                ->leftJoin('cen.units as u', 'e.emp_unt_id', '=', 'u.unt_id');

            /** @var DataScopeService $scopeService */
            $scopeService = app(DataScopeService::class);
            $scopeService->applyScope($builder, $actingUser, 'e.emp_unt_id');

            if ($cleanQuery !== '') {
                $builder->where(function ($w) use ($cleanQuery) {
                    $w->where('e.emp_name', 'ILIKE', "%{$cleanQuery}%")
                      ->orWhere('e.emp_id', 'ILIKE', "%{$cleanQuery}%")
                      ->orWhere('e.emp_cnic', 'ILIKE', "%{$cleanQuery}%")
                      ->orWhere('e.emp_title', 'ILIKE', "%{$cleanQuery}%")
                      ->orWhere('e.emp_rank', 'ILIKE', "%{$cleanQuery}%")
                      ->orWhere('u.unt_name', 'ILIKE', "%{$cleanQuery}%");

                    $simplified = trim(preg_replace('/\b(division|div)\b/i', '', $cleanQuery));
                    if ($simplified !== '' && strtolower($simplified) !== strtolower($cleanQuery)) {
                        $w->orWhere('u.unt_name', 'ILIKE', "%{$simplified}%");
                    }
                });
            }

            if ($cleanStatus !== '') {
                $builder->where('e.emp_status', 'ILIKE', "%{$cleanStatus}%");
            }

            $emps = $builder->select(
                'e.emp_id',
                'e.emp_name',
                'e.emp_cnic',
                'e.emp_rank',
                'e.emp_title',
                'e.emp_status',
                'e.emp_joindt',
                'e.emp_unt_id',
                'u.unt_name as division_name'
            )->orderBy('e.emp_name', 'asc')->limit($limit)->get();

            return [
                'count' => $emps->count(),
                'employees' => $emps->map(fn($e) => [
                    'employee_id' => (string) $e->emp_id,
                    'name' => (string) ($e->emp_name ?? ''),
                    'cnic' => (string) ($e->emp_cnic ?? ''),
                    'rank' => (string) ($e->emp_rank ?? ''),
                    'designation' => (string) ($e->emp_title ?? ''),
                    'status' => (string) ($e->emp_status ?? ''),
                    'joining_date' => (string) ($e->emp_joindt ?? ''),
                    'division_id' => (int) $e->emp_unt_id,
                    'division_name' => (string) ($e->division_name ?? ''),
                ])->all(),
            ];
        });
    }

    /**
     * Tool 12: getEmployeeDetails
     *
     * Retrieves full profile details for an employee within authorized scope.
     */
    public function getEmployeeDetails(string|int $employeeId, Authenticatable|CenAccount|User|null $actingUser): array
    {
        $this->ensureValidScopeContext($actingUser);

        $cleanId = trim((string) $employeeId);
        if ($cleanId === '') {
            throw new InvalidArgumentException('Employee ID or CNIC must not be empty.');
        }

        $emp = DB::table('hr.emps')
            ->where('emp_id', $cleanId)
            ->orWhere('emp_cnic', $cleanId)
            ->first();

        if (!$emp) {
            throw new AiToolRecordNotFoundException("Employee '{$cleanId}' not found.");
        }

        $empUnitId = (int) $emp->emp_unt_id;
        /** @var DataScopeService $scopeService */
        $scopeService = app(DataScopeService::class);
        $isSelf = (string) $actingUser->acc_username === (string) $emp->emp_id
            || (string) ($actingUser->acc_id ?? '') === (string) $emp->emp_id;

        if (!$isSelf && !$scopeService->canAccessUnit($actingUser, $empUnitId)) {
            throw new UnauthorizedScopeException(
                "User '{$actingUser->acc_username}' is not authorized to access employee '{$cleanId}' in division unit {$empUnitId}."
            );
        }

        return HorizonScopeContext::runAs($actingUser, function () use ($emp, $empUnitId, $isSelf, $actingUser) {
            $unit = DB::table('cen.units')->where('unt_id', $empUnitId)->first();

            // Contract info
            $contract = DB::table('hr.contracts')
                ->where('ctr_num', $emp->emp_id)
                ->orderBy('ctr_id', 'desc')
                ->first();

            // Leaves count
            $leavesCount = DB::table('hr.leaves')
                ->where('lve_emp_id', $emp->emp_id)
                ->count();

            // Check if acting user is authorized to see salary
            $context = UserAccessContext::forUser($actingUser);
            $canViewSalary = $context->isSuperAdmin()
                || $context->isCommand()
                || AreaDefinition::isHr(strtolower(trim((string) ($actingUser->acc_untarea ?? ''))))
                || $isSelf;

            return [
                'employee_id' => (string) $emp->emp_id,
                'name' => (string) ($emp->emp_name ?? ''),
                'cnic' => (string) ($emp->emp_cnic ?? ''),
                'rank' => (string) ($emp->emp_rank ?? 'Civilian'),
                'designation' => (string) ($emp->emp_title ?? ''),
                'status' => (string) ($emp->emp_status ?? ''),
                'joining_date' => (string) ($emp->emp_joindt ?? ''),
                'last_date' => (string) ($emp->emp_lastdt ?? ''),
                'division_id' => $empUnitId,
                'division_name' => (string) ($unit->unt_name ?? ''),
                'contract' => $contract ? [
                    'contract_id' => (int) $contract->ctr_id,
                    'start_date' => (string) ($contract->ctr_startdt ?? ''),
                    'end_date' => (string) ($contract->ctr_enddt ?? ''),
                    'salary' => $canViewSalary ? (float) ($contract->ctr_salary ?? 0) : null,
                ] : null,
                'total_leaves_recorded' => $leavesCount,
            ];
        });
    }
}
