<?php

namespace App\Services\Syslog;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns raw trail rows into something an officer can read.
 *
 * The screen must say "Case Status: In Process -> Approved", never
 * "pcs_status: 3 -> 4", and money must look like money.
 *
 * Design: docs/SYSTEM_LOGS_SCHEMA_DESIGN.md §5E.7
 */
class LogPresenter
{
    /** action => [label, bootstrap badge class, icon] */
    private const ACTIONS = [
        'created'            => ['Created',            'success',   'fa-plus-circle'],
        'updated'            => ['Updated',            'primary',   'fa-pen'],
        'deleted'            => ['Deleted',            'danger',    'fa-trash'],
        'truncated'          => ['Table Emptied',      'danger',    'fa-bomb'],
        'restored'           => ['Restored',           'info',      'fa-undo-alt'],
        'approved'           => ['Approved',           'success',   'fa-check-circle'],
        'rejected'           => ['Rejected',           'danger',    'fa-times-circle'],
        'returned'           => ['Returned',           'warning',   'fa-reply'],
        'released'           => ['Released',           'info',      'fa-paper-plane'],
        'cancelled'          => ['Cancelled',          'secondary', 'fa-ban'],
        'forwarded'          => ['Forwarded',          'primary',   'fa-arrow-right'],
        'reopened'           => ['Reopened',           'info',      'fa-folder-open'],
        'login'              => ['Login',              'light',     'fa-sign-in-alt'],
        'login_failed'       => ['Login Failed',       'danger',    'fa-user-lock'],
        'logout'             => ['Logout',             'light',     'fa-sign-out-alt'],
        'password_changed'   => ['Password Changed',   'warning',   'fa-key'],
        'password_forced'    => ['Password Reset',     'warning',   'fa-key'],
        'denied'             => ['Access Denied',      'danger',    'fa-hand-paper'],
        'permission_changed' => ['Permissions Changed','danger',    'fa-user-shield'],
        'role_changed'       => ['Role Changed',       'danger',    'fa-user-tag'],
        'account_created'    => ['Account Created',    'success',   'fa-user-plus'],
        'account_disabled'   => ['Account Closed',     'secondary', 'fa-user-slash'],
        'uploaded'           => ['File Uploaded',      'info',      'fa-upload'],
        'downloaded'         => ['File Downloaded',    'light',     'fa-download'],
        'deleted_file'       => ['File Deleted',       'danger',    'fa-file-excel'],
        'exported'           => ['Exported',           'light',     'fa-file-export'],
        'printed'            => ['Printed',            'light',     'fa-print'],
        'viewed_sensitive'   => ['Sensitive View',     'warning',   'fa-eye'],
        'imported'           => ['Imported',           'info',      'fa-file-import'],
        'job_ran'            => ['Job Ran',            'light',     'fa-robot'],
        'setting_changed'    => ['Setting Changed',    'warning',   'fa-sliders-h'],
        'ddl'                => ['Schema Change',      'danger',    'fa-database'],
    ];

    private const SOURCES = [
        'app'      => ['Application', 'primary'],
        'legacy'   => ['Legacy App',  'warning'],
        'console'  => ['Console',     'secondary'],
        'db'       => ['Direct DB',   'danger'],
        'job'      => ['Scheduled',   'info'],
        'backfill' => ['Imported History', 'light'],
    ];

    /** Columns whose value is a rupee amount. */
    private const MONEY = [
        'pcs_price', 'pcs_intprice', 'pcs_midprice', 'pcs_inttax', 'pcs_midtax',
        'sbh_alloc', 'cmt_amount', 'trn_amount1', 'trn_amount2', 'trn_tax1',
        'trn_balance', 'trf_amount', 'ctc_price', 'ctc_newsalary', 'ctc_approvedsalary',
        'ctc_newprobsal', 'ctc_approvedprobsal', 'prj_propcost', 'prj_aprvcost',
        'prj_cfycost', 'sor_salary', 'sor_netsalary', 'sor_grosalary', 'sor_ctrsalary',
        'sor_arrears', 'sor_dues', 'sor_penalty', 'sor_award', 'sor_withheld',
        'sor_loaned', 'sor_overwork', 'sor_underwork', 'sor_paidalready',
        'srq_salary', 'srq_netsalary', 'srq_grosalary', 'srq_arrears', 'srq_dues',
        'srq_penalty', 'srq_award', 'srq_withheld', 'srq_loaned',
        'pci_price', 'pci_estprice', 'qti_price',
    ];

    private array $labelCache = [];

    public function actionLabel(?string $a): string
    {
        return self::ACTIONS[$a][0] ?? Str::headline((string) $a);
    }

    public function actionClass(?string $a): string
    {
        return self::ACTIONS[$a][1] ?? 'secondary';
    }

    public function actionIcon(?string $a): string
    {
        return self::ACTIONS[$a][2] ?? 'fa-circle';
    }

    public function sourceLabel(?string $s): string
    {
        return self::SOURCES[$s][0] ?? (string) $s;
    }

    public function sourceClass(?string $s): string
    {
        return self::SOURCES[$s][1] ?? 'secondary';
    }

    public function severityLabel(int $s): string
    {
        return match ($s) { 2 => 'Critical', 1 => 'Sensitive', default => 'Normal' };
    }

    public function severityClass(int $s): string
    {
        return match ($s) { 2 => 'danger', 1 => 'warning', default => 'light' };
    }

    /** All available action keys, for the filter dropdown. */
    public function allActions(): array
    {
        $out = [];
        foreach (self::ACTIONS as $k => $v) {
            $out[$k] = $v[0];
        }
        return $out;
    }

    /** Human label for a column, from syslog.watched_tables.wt_labels. */
    public function fieldLabel(?string $schema, ?string $table, ?string $column, ?string $stored = null): string
    {
        if ($stored !== null && $stored !== '') {
            return $stored;
        }
        if (! $column) {
            return '';
        }

        $key = $schema . '.' . $table;
        if (! array_key_exists($key, $this->labelCache)) {
            try {
                $row = DB::selectOne(
                    'SELECT wt_labels FROM syslog.watched_tables WHERE wt_schema = ? AND wt_table = ?',
                    [$schema, $table]
                );
                $this->labelCache[$key] = $row && $row->wt_labels
                    ? (json_decode($row->wt_labels, true) ?: [])
                    : [];
            } catch (\Throwable) {
                $this->labelCache[$key] = [];
            }
        }

        return $this->labelCache[$key][$column] ?? Str::headline(preg_replace('/^[a-z]{3,4}_/', '', $column));
    }

    /** Format a value for display: money as money, dates as dates, null as a dash. */
    public function value(?string $raw, ?string $column = null): string
    {
        if ($raw === null || $raw === '') {
            return '<span class="sl-null">&mdash; not set &mdash;</span>';
        }

        if ($column && in_array($column, self::MONEY, true) && is_numeric($raw)) {
            return '<span class="sl-money">Rs ' . $this->inr((float) $raw) . '</span>';
        }

        if ($raw === 't' || $raw === 'true')  { return '<span class="sl-bool">Yes</span>'; }
        if ($raw === 'f' || $raw === 'false') { return '<span class="sl-bool">No</span>'; }

        if (preg_match('/^\d{4}-\d{2}-\d{2}([ T]\d{2}:\d{2}:\d{2})?/', $raw)) {
            try {
                $c = Carbon::parse($raw);
                return e(strlen($raw) <= 10 ? $c->format('d M Y') : $c->format('d M Y, H:i'));
            } catch (\Throwable) {
                // fall through
            }
        }

        return e(Str::limit($raw, 400));
    }

    /** Indian/Pakistani digit grouping: 24,00,000 rather than 2,400,000. */
    public function inr(float $n): string
    {
        $neg = $n < 0;
        $n = abs($n);
        $whole = (string) (int) floor($n);
        $frac = round($n - floor($n), 2);

        if (strlen($whole) > 3) {
            $last3 = substr($whole, -3);
            $rest = substr($whole, 0, -3);
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $whole = $rest . ',' . $last3;
        }

        return ($neg ? '-' : '') . $whole . ($frac > 0 ? '.' . str_pad((string) (int) round($frac * 100), 2, '0', STR_PAD_LEFT) : '');
    }

    public function dtg($v, string $fmt = 'd M Y, H:i:s'): string
    {
        if (! $v) {
            return '';
        }
        try {
            return Carbon::parse($v)->format($fmt);
        } catch (\Throwable) {
            return (string) $v;
        }
    }

    public function ago($v): string
    {
        if (! $v) {
            return '';
        }
        try {
            return Carbon::parse($v)->diffForHumans();
        } catch (\Throwable) {
            return '';
        }
    }

    /** "3 fields", or the one change spelled out when there is only one. */
    public function changeSummary(object $row): string
    {
        $n = (int) ($row->fields_changed ?? 0);
        $rows = (int) ($row->rows_touched ?? 0);
        $files = (int) ($row->files_touched ?? 0);

        $bits = [];
        if ($n > 0)     { $bits[] = $n . ' ' . Str::plural('field', $n); }
        if ($rows > 0)  { $bits[] = $rows . ' ' . Str::plural('row', $rows); }
        if ($files > 0) { $bits[] = $files . ' ' . Str::plural('file', $files); }

        return $bits ? implode(' &middot; ', $bits) : '<span class="sl-null">&mdash;</span>';
    }

    /** A person as "Cdr PN Khalid Mahmood (DLOG)". */
    public function person(?string $name, ?string $rank = null, ?string $desig = null): string
    {
        $s = trim(($rank ? $rank . ' ' : '') . ($name ?: ''));
        if ($desig) {
            $s .= ' (' . $desig . ')';
        }
        return $s !== '' ? e($s) : '<span class="sl-null">&mdash;</span>';
    }
}
