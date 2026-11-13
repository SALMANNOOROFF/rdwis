<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Auth\PermissionRegistry;
use App\Services\Syslog\Audit;
use App\Services\Syslog\LogPresenter;
use App\Services\Syslog\LogQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * System Logs -- read-only view of the audit trail.
 *
 * Only GET routes exist for this controller. There is no store/update/destroy,
 * by design: the trail is append-only at the database level as well
 * (syslog deny-triggers + the app role holding SELECT only).
 *
 * Design: docs/SYSTEM_LOGS_SCHEMA_DESIGN.md §5E
 */
class SystemLogController extends Controller
{
    public function __construct(
        private LogQueryService $q,
        private LogPresenter $p,
    ) {
    }

    /** Level 1 -- category tiles. */
    public function home(Request $request)
    {
        $this->authorize(PermissionRegistry::SYSLOG_VIEW);

        return view('admin.syslog.home', [
            'categories' => $this->q->categories(),
            'overview'   => $this->q->overview(),
            'p'          => $this->p,
        ]);
    }

    /** Level 2 -- one category's event list. */
    public function category(Request $request, string $category)
    {
        $this->authorize(PermissionRegistry::SYSLOG_VIEW);

        if ($category === 'legacy') {
            return view('admin.syslog.legacy', [
                'rows' => $this->q->legacy($request),
                'p'    => $this->p,
            ]);
        }

        if ($category === 'health') {
            $this->authorize(PermissionRegistry::SYSLOG_VIEW_ALL);

            return view('admin.syslog.health', [
                'health'   => $this->q->health(),
                'overview' => $this->q->overview(),
                'p'        => $this->p,
            ]);
        }

        $cat = $this->q->category($category);
        abort_if(! $cat, 404, 'Unknown log category.');

        $objects = $this->parsePgArray($cat->cat_objects);
        $result = $this->q->events($request, $objects);

        return view('admin.syslog.category', [
            'cat'     => $cat,
            'objects' => $objects,
            'result'  => $result,
            'actions' => $this->p->allActions(),
            'filters' => $request->query(),
            'p'       => $this->p,
        ]);
    }

    /** Level 3 -- one record, six tabs. */
    public function record(Request $request, string $category, string $object, string $id)
    {
        $this->authorize(PermissionRegistry::SYSLOG_VIEW);

        $header = $this->q->recordHeader($object, $id);
        abort_if(! $header || (int) $header->events === 0, 404, 'No log entries for this record.');

        $tab = in_array($request->query('tab'), ['timeline', 'changes', 'custody', 'snapshots', 'files', 'people'], true)
            ? $request->query('tab')
            : 'timeline';

        $data = [
            'category' => $category,
            'object'   => $object,
            'id'       => $id,
            'header'   => $header,
            'tab'      => $tab,
            'p'        => $this->p,
        ];

        switch ($tab) {
            case 'changes':
                $data['changes'] = $this->q->changes($object, $id, $request->query('field') ?: null);
                $data['rows'] = $this->q->rowChanges($object, $id);
                break;
            case 'custody':
                $data['custody'] = $this->q->custody($object, $id);
                break;
            case 'snapshots':
                $data['snapshots'] = $this->q->snapshots($object, $id);
                $data['diff'] = null;
                if ($request->filled('from') && $request->filled('to')) {
                    $data['diff'] = $this->q->snapshotDiff(
                        (int) $request->query('from'),
                        (int) $request->query('to')
                    );
                    $data['diffFrom'] = (int) $request->query('from');
                    $data['diffTo'] = (int) $request->query('to');
                }
                if ($request->filled('view')) {
                    $data['doc'] = $this->q->snapshotDoc((int) $request->query('view'));
                    $data['docId'] = (int) $request->query('view');
                }
                break;
            case 'files':
                $data['files'] = $this->q->files($object, $id);
                break;
            case 'people':
                $data['people'] = $this->q->people($object, $id);
                break;
            default:
                $data['trail'] = $this->q->trail($object, $id);
        }

        return view('admin.syslog.record', $data);
    }

    /** One event in full, including its raw before/after rows. */
    public function event(Request $request, int $sevId)
    {
        $this->authorize(PermissionRegistry::SYSLOG_VIEW);

        $event = $this->q->event($sevId);
        abort_if(! $event, 404);

        return view('admin.syslog.event', [
            'event'   => $event,
            'changes' => DB::select(
                'SELECT * FROM syslog.field_changes WHERE fc_sev_id = ? ORDER BY fc_id',
                [$sevId]
            ),
            'rows' => DB::select(
                'SELECT rc_id, rc_schema, rc_table, rc_op, rc_pk_text, rc_changed,
                        jsonb_pretty(rc_old_row) AS old_row, jsonb_pretty(rc_new_row) AS new_row
                   FROM syslog.row_changes WHERE rc_sev_id = ? ORDER BY rc_seq, rc_id',
                [$sevId]
            ),
            'p' => $this->p,
        ]);
    }

    /** Printable sheet for one record. The print itself is logged. */
    public function print(Request $request, string $category, string $object, string $id)
    {
        $this->authorize(PermissionRegistry::SYSLOG_VIEW);

        $header = $this->q->recordHeader($object, $id);
        abort_if(! $header || (int) $header->events === 0, 404);

        Audit::log('printed', [
            'object'  => $object,
            'objid'   => $id,
            'objref'  => $header->objref,
            'remarks' => 'System Logs sheet printed',
        ]);
        Audit::end();

        return view('admin.syslog.print', [
            'category' => $category,
            'object'   => $object,
            'id'       => $id,
            'header'   => $header,
            'trail'    => $this->q->trail($object, $id),
            'changes'  => $this->q->changes($object, $id),
            'custody'  => $this->q->custody($object, $id),
            'files'    => $this->q->files($object, $id),
            'verify'   => $this->q->overview()->verify,
            'p'        => $this->p,
        ]);
    }

    /** Streamed CSV of the current filter. The export itself is logged. */
    public function export(Request $request, string $category): StreamedResponse
    {
        $this->authorize(PermissionRegistry::SYSLOG_EXPORT);

        $cat = $this->q->category($category);
        abort_if(! $cat, 404);

        $objects = $this->parsePgArray($cat->cat_objects);

        Audit::log('exported', [
            'object'  => 'system',
            'objref'  => 'System Logs: ' . $cat->cat_label,
            'remarks' => 'CSV export, filters: ' . json_encode($request->query()),
        ]);
        Audit::end();

        $result = $this->q->events($request, $objects);
        $filename = 'system-logs-' . $category . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($result) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Event ID', 'Date & Time', 'Username', 'Name', 'Rank', 'Designation', 'Unit',
                'Role', 'Action', 'Module', 'Record ID', 'Reference', 'Stage From', 'Stage To',
                'Sent From', 'Sent To', 'Authority Ref', 'Remarks', 'IP', 'Route', 'Source',
                'Result', 'Severity', 'Rows', 'Fields', 'Files',
            ]);
            foreach ($result['rows'] as $r) {
                fputcsv($out, [
                    $r->sev_id, $r->sev_dtg, $r->sev_username, $r->sev_name, $r->sev_rank,
                    $r->sev_desig, $r->sev_untname, $r->sev_role_slug, $r->sev_action,
                    $r->sev_object, $r->sev_objid, $r->sev_objref, $r->sev_stage_from,
                    $r->sev_stage_to, $r->sev_from_holder, $r->sev_to_holder,
                    $r->sev_authority_ref, $r->sev_remarks, $r->sev_ip, $r->sev_route,
                    $r->sev_source, $r->sev_result, $r->sev_severity,
                    $r->rows_touched, $r->fields_changed, $r->files_touched,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Run the chain verification on demand. */
    public function verify(Request $request)
    {
        $this->authorize(PermissionRegistry::SYSLOG_VERIFY);

        try {
            $row = DB::selectOne("SELECT * FROM syslog.verify_seals('manual')");
            $msg = ($row->status ?? '?') === 'ok'
                ? 'Chain verified: ' . (int) ($row->seals_checked ?? 0) . ' seal(s) intact, no event modified.'
                : 'CHAIN BROKEN. ' . ($row->detail ?? '');

            return back()->with(($row->status ?? '') === 'ok' ? 'status' : 'error', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Verification failed: ' . $e->getMessage());
        }
    }

    /** text[] from PostgreSQL arrives as the literal {a,b,c}. */
    private function parsePgArray(?string $v): array
    {
        if (! $v) {
            return [];
        }
        $v = trim($v, '{}');
        if ($v === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($s) => trim($s, " \t\n\r\0\x0B\""),
            str_getcsv($v)
        )));
    }
}
