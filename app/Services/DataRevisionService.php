<?php

namespace App\Services;

use App\Enums\RevType;
use App\Models\AudRev;
use App\Models\AudRevComp;
use App\Models\AudRevData;
use Illuminate\Support\Facades\DB;

class DataRevisionService
{
    /**
     * Map legacy unqualified / underscore table names to qualified PostgreSQL schema.table.
     */
    protected static array $tableMap = [
        'pur_purcases'        => 'pur.purcases',
        'pur_purcaseitems'    => 'pur.purcaseitems',
        'pur_quotes'          => 'pur.quotes',
        'pur_quoteitems'      => 'pur.quoteitems',
        'fin_commitments'     => 'fin.commitments',
        'fin_transactions'    => 'fin.transactions',
        'pur_purreceipts'     => 'pur.purreceipts',
        'pur_purreceiptitems' => 'pur.purreceiptitems',
        'pur_purattachments'  => 'pur.purattachments',
        'fin_salorders'       => 'fin.salorders',
        'fin_salorders_shd'   => 'fin.salorders_shd',
        'hr_salreqs'          => 'hr.salreqs',
        'fin_sharesalloc'     => 'fin.sharesalloc',
        'fin_transfers'       => 'fin.transfers',
        'fin_sharesinstall'   => 'fin.sharesinstall',
        'hr_emps'             => 'hr.emps',
        'hr_contracts'        => 'hr.contracts',
        'prj_milestones'      => 'prj.milestones',
        'pur_purcases_shd'    => 'pur.purcases_shd',
        'aud_revs'            => 'aud.revs',
        'aud_revcomps'        => 'aud.revcomps',
        'aud_revdata'         => 'aud.revdata',
    ];

    /**
     * Resolve a table name to its schema-qualified PostgreSQL counterpart.
     */
    public function resolveTable(string $table): string
    {
        if (str_contains($table, '.')) {
            return $table;
        }

        if (isset(self::$tableMap[$table])) {
            return self::$tableMap[$table];
        }

        if (preg_match('/^(pur|fin|hr|cen|prj|aud|sup)_(.*)$/', $table, $matches)) {
            return "{$matches[1]}.{$matches[2]}";
        }

        return $table;
    }

    /**
     * 1. CreateDataRevision (Audit.bas:46-76)
     *
     * Creates an aud.revs header record and delegates to CreateDRComps or CreateDRData.
     */
    public function createDataRevision(
        string $revObject,
        $objectId,
        int $unitId,
        RevType|int $revType,
        ?string $revRef = null,
        ?string $revObjectExt = null,
        ?int $intUnitId = null,
        ?string $revReason = null,
        array $fieldDiffs = []
    ): AudRev {
        $typeEnum = $revType instanceof RevType ? $revType : RevType::from($revType);
        $user = auth()->user();
        $initiatingUnitId = $intUnitId ?? ($user->acc_unt_id ?? $unitId);

        return DB::transaction(function () use (
            $revObject,
            $objectId,
            $unitId,
            $typeEnum,
            $revRef,
            $revObjectExt,
            $initiatingUnitId,
            $revReason,
            $fieldDiffs
        ) {
            $revision = AudRev::create([
                'rev_date'       => now()->toDateString(),
                'rev_type'       => $typeEnum,
                'rev_intunt_id'  => $initiatingUnitId,
                'rev_obj'        => $revObject,
                'rev_objid'      => (string) $objectId,
                'rev_unt_id'     => $unitId,
                'rev_status'     => 'Draft',
                'rev_ref'        => $revRef,
                'rev_objext'     => $revObjectExt,
                'rev_reason'     => $revReason,
            ]);

            if ($typeEnum->isCascade()) {
                $this->createDRComps($typeEnum->value, $revision->rev_id, $revObject, $objectId);
            } elseif ($typeEnum === RevType::FIELD_LEVEL) {
                $this->createDRData($revision->rev_id, $fieldDiffs);
            }

            return $revision;
        });
    }

    /**
     * 2. CreateDRComps (Audit.bas:78-246)
     *
     * Constructs child component snapshots across all 9 object branches.
     */
    public function createDRComps(int $revType, int $revId, string $object, $objId): void
    {
        $key = "{$object} - {$revType}";

        switch ($key) {
            case 'Purchase Case - 1':
                // 1. Purchase Case + Items + Quotations bundled into pcs_rev (Audit.bas:90-112)
                $objectData = $object . "\r\n" . $this->copyRowData('pur_purcases', 'pcs_id', $objId);

                $caseItems = DB::table('pur.purcaseitems')
                    ->where('pci_pcs_id', $objId)
                    ->orderBy('pci_serial')
                    ->get();

                if ($caseItems->isNotEmpty()) {
                    $objectData .= "\r\nPurchase Case Items";
                    foreach ($caseItems as $ci) {
                        $objectData .= "\r\n" . $this->copyRowData('pur_purcaseitems', 'pci_id', $ci->pci_id);
                    }
                }

                $quotes = DB::table('pur.quotes')
                    ->where('qte_pcs_id', $objId)
                    ->get();

                foreach ($quotes as $qte) {
                    $objectData .= "\r\nQuotation\r\n" . $this->copyRowData('pur_quotes', 'qte_id', $qte->qte_id);
                    $quoteItems = DB::table('pur.quoteitems')
                        ->where('qti_qte_id', $qte->qte_id)
                        ->orderBy('qti_serial')
                        ->get();

                    $objectData .= "\r\nQuote Items";
                    foreach ($quoteItems as $qi) {
                        $objectData .= "\r\n" . $this->copyRowData('pur_quoteitems', 'qti_id', $qi->qti_id);
                    }
                }

                $this->makeDRCompEntry($revId, 'pur_purcases', $objId, $objectData, 'pcs_rev', 1);

                // 2. Commitments and Payments (Audit.bas:114-127)
                $pc = DB::table('pur.purcases')->where('pcs_id', $objId)->first(['pcs_type']);
                $pcsType = $pc->pcs_type ?? '';

                $commitments = DB::table('fin.commitments')
                    ->where('cmt_docid', $objId)
                    ->where('cmt_type', $pcsType)
                    ->get(['cmt_id']);

                foreach ($commitments as $cmt) {
                    $cmtData = "Commitment\r\n" . $this->copyRowData('fin_commitments', 'cmt_id', $cmt->cmt_id);
                    $this->makeDRCompEntry($revId, 'fin_commitments', $cmt->cmt_id, $cmtData, 'cmt_del', 2);

                    $transactions = DB::table('fin.transactions')
                        ->where('trn_cmt_id', $cmt->cmt_id)
                        ->get(['trn_id']);

                    foreach ($transactions as $trn) {
                        $trnData = "Payment\r\n" . $this->copyRowData('fin_transactions', 'trn_id', $trn->trn_id);
                        $this->makeDRCompEntry($revId, 'fin_transactions', $trn->trn_id, $trnData, 'trn_del', 2);
                    }
                }

                // 3. Receipts and Receipt Items (Audit.bas:129-140)
                $receipts = DB::table('pur.purreceipts')
                    ->where('prt_pcs_id', $objId)
                    ->get(['prt_id']);

                foreach ($receipts as $prt) {
                    $prtData = "Receipt\r\n" . $this->copyRowData('pur_purreceipts', 'prt_id', $prt->prt_id);
                    $receiptItems = DB::table('pur.purreceiptitems')
                        ->where('pti_prt_id', $prt->prt_id)
                        ->orderBy('pti_serial')
                        ->get();

                    if ($receiptItems->isNotEmpty()) {
                        $prtData .= "\r\nReceipt Items";
                        foreach ($receiptItems as $ri) {
                            $prtData .= "\r\n" . $this->copyRowData('pur_purreceiptitems', 'pti_id', $ri->pti_id);
                        }
                    }

                    $this->makeDRCompEntry($revId, 'pur_purreceipts', $prt->prt_id, $prtData, 'prt_del', 2);
                }

                // 4. Attachments (Audit.bas:142-149)
                $attachments = DB::table('pur.purattachments')
                    ->where('pat_objtype', 'pcs')
                    ->where('pat_objid', $objId)
                    ->whereNotNull('pat_path')
                    ->get(['pat_id']);

                foreach ($attachments as $att) {
                    $attData = "Attachment\r\n" . $this->copyRowData('pur_purattachments', 'pat_id', $att->pat_id);
                    $this->makeDRCompEntry($revId, 'pur_purattachments', $att->pat_id, $attData, 'pat_del', 2);
                }
                break;

            case 'Salary Order - 1':
                // 1. Salary Order (Audit.bas:152-154)
                $objectData = $object . "\r\n" . $this->copyRowData('fin_salorders', 'sor_id', $objId);
                $this->makeDRCompEntry($revId, 'fin_salorders', $objId, $objectData, 'sor_rev', 1);

                // 2. Salary Requisition (Audit.bas:156-159)
                $order = DB::table('fin.salorders')->where('sor_id', $objId)->first(['sor_srq_id', 'sor_type']);
                $srqId = $order->sor_srq_id ?? 0;
                $sorType = $order->sor_type ?? 'Sa';

                $srqData = "Salary Requisition\r\n" . $this->copyRowData('hr_salreqs', 'srq_id', $srqId);
                $this->makeDRCompEntry($revId, 'hr_salreqs', $srqId, $srqData, 'srq_rev', 2);

                // 3. Commitments and Payments (Audit.bas:161-172)
                $commitments = DB::table('fin.commitments')
                    ->where('cmt_docid', $objId)
                    ->where('cmt_type', $sorType)
                    ->get(['cmt_id']);

                foreach ($commitments as $cmt) {
                    $cmtData = "Commitment\r\n" . $this->copyRowData('fin_commitments', 'cmt_id', $cmt->cmt_id);
                    $this->makeDRCompEntry($revId, 'fin_commitments', $cmt->cmt_id, $cmtData, 'cmt_del', 2);

                    $transactions = DB::table('fin.transactions')
                        ->where('trn_cmt_id', $cmt->cmt_id)
                        ->get(['trn_id']);

                    foreach ($transactions as $trn) {
                        $trnData = "Payment\r\n" . $this->copyRowData('fin_transactions', 'trn_id', $trn->trn_id);
                        $this->makeDRCompEntry($revId, 'fin_transactions', $trn->trn_id, $trnData, 'trn_del', 2);
                    }
                }
                break;

            case 'Allocation - 3':
                // 1. Allocation (Audit.bas:176-177)
                $objectData = $object . "\r\n" . $this->copyRowData('fin_sharesalloc', 'sha_id', $objId);
                $this->makeDRCompEntry($revId, 'fin_sharesalloc', $objId, $objectData, 'alc_del', 1);

                $alloc = DB::table('fin.sharesalloc')->where('sha_id', $objId)->first(['sha_ficmt_id', 'sha_focmt_id']);
                $ficmtId = $alloc->sha_ficmt_id ?? null;
                $focmtId = $alloc->sha_focmt_id ?? null;

                // 2. Commitment In (Audit.bas:181-185)
                if ($ficmtId) {
                    $cmtInData = "Commitment Alloc\r\n" . $this->copyRowData('fin_commitments', 'cmt_id', $ficmtId);
                    $this->makeDRCompEntry($revId, 'fin_commitments', $ficmtId, $cmtInData, 'cmt_del', 3);

                    $cmtIn = DB::table('fin.commitments')->where('cmt_id', $ficmtId)->first(['cmt_docid']);
                    $trfInId = $cmtIn->cmt_docid ?? null;

                    // 4. Transfer In (Audit.bas:191-193)
                    if ($trfInId) {
                        $trfInData = "Transfer Alloc\r\n" . $this->copyRowData('fin_transfers', 'trf_id', $trfInId);
                        $this->makeDRCompEntry($revId, 'fin_transfers', $trfInId, $trfInData, 'trf_del', 3);
                    }
                }

                // 3. Commitment Out (Audit.bas:186-190)
                if ($focmtId) {
                    $cmtOutData = "Commitment MTSS\r\n" . $this->copyRowData('fin_commitments', 'cmt_id', $focmtId);
                    $this->makeDRCompEntry($revId, 'fin_commitments', $focmtId, $cmtOutData, 'cmt_del', 3);

                    $cmtOut = DB::table('fin.commitments')->where('cmt_id', $focmtId)->first(['cmt_docid']);
                    $trfOutId = $cmtOut->cmt_docid ?? null;

                    // 5. Transfer Out (Audit.bas:194-196)
                    if ($trfOutId) {
                        $trfOutData = "Transfer MTSS\r\n" . $this->copyRowData('fin_transfers', 'trf_id', $trfOutId);
                        $this->makeDRCompEntry($revId, 'fin_transfers', $trfOutId, $trfOutData, 'trf_del', 3);
                    }
                }
                break;

            case 'Funding - 3':
                // 1. Funding (Audit.bas:200-201)
                $objectData = $object . "\r\n" . $this->copyRowData('fin_sharesinstall', 'shi_id', $objId);
                $this->makeDRCompEntry($revId, 'fin_sharesinstall', $objId, $objectData, 'fnd_del', 1);

                $fnd = DB::table('fin.sharesinstall')->where('shi_id', $objId)->first(['shi_fitrn_id', 'shi_fotrn_id']);
                $fitrnId = $fnd->shi_fitrn_id ?? null;
                $fotrnId = $fnd->shi_fotrn_id ?? null;

                // 2. Transfer In (Audit.bas:205-207)
                if ($fitrnId) {
                    $trnInData = "Funding Received\r\n" . $this->copyRowData('fin_transactions', 'trn_id', $fitrnId);
                    $this->makeDRCompEntry($revId, 'fin_transactions', $fitrnId, $trnInData, 'trn_del', 3);
                }

                // 3. Transfer Out (Audit.bas:209-211)
                // Note: Legacy Audit.bas line 211 uses 'fin_commitments' as table parameter for trn_del action
                if ($fotrnId) {
                    $trnOutData = "Payment MTSS\r\n" . $this->copyRowData('fin_transactions', 'trn_id', $fotrnId);
                    $this->makeDRCompEntry($revId, 'fin_commitments', $fotrnId, $trnOutData, 'trn_del', 3);
                }
                break;

            case 'Payment - 3':
                // 1. Payment (Audit.bas:216-217)
                $objectData = $object . "\r\n" . $this->copyRowData('fin_transactions', 'trn_id', $objId);
                $this->makeDRCompEntry($revId, 'fin_transactions', $objId, $objectData, 'trn_del', 1);

                // 2. Commitment (Audit.bas:219-221)
                $trn = DB::table('fin.transactions')->where('trn_id', $objId)->first(['trn_cmt_id']);
                $cmtId = $trn->trn_cmt_id ?? null;
                if ($cmtId) {
                    $cmtData = "Commitment\r\n" . $this->copyRowData('fin_commitments', 'cmt_id', $cmtId);
                    $this->makeDRCompEntry($revId, 'fin_commitments', $cmtId, $cmtData, 'cmt_rev', 2);
                }
                break;

            case 'Employee - 1':
                // 1. Employee (Audit.bas:225-226)
                $objectData = $object . "\r\n" . $this->copyRowData('hr_emps', 'emp_id', $objId);
                $this->makeDRCompEntry($revId, 'hr_emps', $objId, $objectData, 'emp_rev', 1);

                // 2. Contract (Audit.bas:228-229)
                // Legacy Audit.bas passes emp_id ($objId) to CopyRowData for integer column 'ctr_id',
                // and passes $objId as rvc_rowid. This produces an empty data string (guarded against
                // Postgres 22P02) resulting in just the header ("Employee\r\n"). This is preserved for
                // exact bug-for-bug parity with legacy VBA.
                $objectData = $object . "\r\n" . $this->copyRowData('hr_contracts', 'ctr_id', $objId);
                $this->makeDRCompEntry($revId, 'hr_contracts', $objId, $objectData, 'ctr_rev', 2);
                break;

            case 'Contract - 1':
                // 1. Contract (Audit.bas:233-234)
                $objectData = $object . "\r\n" . $this->copyRowData('hr_contracts', 'ctr_id', $objId);
                $this->makeDRCompEntry($revId, 'hr_contracts', $objId, $objectData, 'ctr_del', 1);
                break;

            case 'Commitment - 1':
                // 1. Commitment (Audit.bas:237-238)
                $objectData = $object . "\r\n" . $this->copyRowData('fin_commitments', 'cmt_id', $objId);
                $this->makeDRCompEntry($revId, 'fin_commitments', $objId, $objectData, 'cmt_rev', 1);
                break;

            case 'Task - 1':
                // 1. Task / Milestone (Audit.bas:241-242)
                $objectData = $object . "\r\n" . $this->copyRowData('prj_milestones', 'msn_idd', $objId);
                $this->makeDRCompEntry($revId, 'prj_milestones', $objId, $objectData, 'msn_idd', 1);
                break;

            default:
                throw new \InvalidArgumentException("Unsupported cascade combination: {$key}");
        }
    }

    /**
     * 3. MakeDRCompEntry (Audit.bas:248-261)
     *
     * Inserts a record into aud.revcomps with legacy quote sanitization.
     */
    public function makeDRCompEntry(
        int $revId,
        string $tableName,
        $rowId,
        string $rowDetail,
        string $actionName,
        int $revType
    ): AudRevComp {
        $sanitizedDetail = str_replace(["'", '"'], ["`", '``'], $rowDetail);

        return AudRevComp::create([
            'rvc_rev_id' => $revId,
            'rvc_table'  => $tableName,
            'rvc_rowid'  => (string) $rowId,
            'rvc_action' => $actionName,
            'rvc_type'   => $revType,
            'rvc_detail' => $sanitizedDetail,
        ]);
    }

    /**
     * 4. CreateDRData (Audit.bas:263-371)
     *
     * Inserts field diff rows into aud.revdata for type 2 field-level revisions.
     */
    public function createDRData(int $revId, array $diffs): void
    {
        foreach ($diffs as $diff) {
            AudRevData::create([
                'rvd_rev_id'     => $revId,
                'rvd_table'      => $diff['table'] ?? '',
                'rvd_rowid'      => (string) ($diff['rowid'] ?? ''),
                'rvd_attrib'     => $diff['attrib'] ?? '',
                'rvd_oldvalue'   => isset($diff['oldvalue']) ? (string) $diff['oldvalue'] : null,
                'rvd_newvalue'   => isset($diff['newvalue']) ? (string) $diff['newvalue'] : null,
                'rvd_datatype'   => $diff['datatype'] ?? 'Text',
                'rvd_type'       => (int) ($diff['type'] ?? 1),
                'rvd_conversion' => $diff['conversion'] ?? null,
                'rvd_colname'    => $diff['colname'] ?? null,
                'rvd_alias'      => $diff['alias'] ?? null,
            ]);
        }
    }

    /**
     * 5. CopyRowData (Audit.bas:415-431)
     *
     * Fetches a single database row and serializes its fields into:
     * "col1: val1, col2: val2, ..."
     */
    public function copyRowData(string $tableName, string $primKey, $recordId): string
    {
        // Guard against Postgres 22P02 (invalid input syntax for type integer)
        // when legacy code queries an integer column with a non-numeric string (e.g. Audit.bas:228)
        if (! is_numeric($recordId) && in_array($primKey, [
            'ctr_id', 'pcs_id', 'sor_id', 'cmt_id', 'trn_id', 'prt_id',
            'trf_id', 'sha_id', 'shi_id', 'msn_idd', 'srq_id', 'pci_id',
            'qte_id', 'qti_id', 'pti_id', 'pat_id', 'unt_id', 'hed_id',
        ], true)) {
            return '';
        }

        $resolvedTable = $this->resolveTable($tableName);
        $row = DB::table($resolvedTable)->where($primKey, $recordId)->first();

        if (! $row) {
            return '';
        }

        $parts = [];
        foreach ((array) $row as $col => $val) {
            $formattedVal = $this->formatRowValue($val);
            $parts[] = "{$col}: {$formattedVal}";
        }

        return implode(', ', $parts);
    }

    /**
     * Format a single column value for legacy-compatible CopyRowData representation.
     */
    protected function formatRowValue($val): string
    {
        if ($val === null) {
            return '';
        }

        if (is_bool($val)) {
            return $val ? '1' : '0';
        }

        if ($val instanceof \DateTimeInterface) {
            return $val->format('d-M-Y');
        }

        return (string) $val;
    }
}
