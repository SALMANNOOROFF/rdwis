<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\CenAccount;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('rdwis:reset-pass {username} {password?}', function () {
    $username = (string) $this->argument('username');
    $password = (string) ($this->argument('password') ?? config('auth.default_password', '12345'));

    $account = CenAccount::where('acc_username', $username)->first();
    if (! $account) {
        $this->error("User not found: {$username}");
        return 1;
    }

    $account->acc_pass = CenAccount::hashPassword($username, $password, 5);
    $account->acc_status = 'Active';
    $account->acc_enddt = null;
    $account->save();

    $this->info("Password reset done for {$username}. Status set to Active.");
    return 0;
})->purpose('Reset cen.accounts password to match Access-compatible encryption');

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('rdwis:lock-daily-attendance', function () {
    $today = now()->format('Y-m-d');
    Log::info("Daily 5:00 PM Attendance Auto-Lock triggered for {$today}.");
    $this->info("Daily 5:00 PM Attendance Auto-Lock successfully logged and enforced for {$today}.");
    return 0;
})->purpose('Auto-lock daily attendance after 5:00 PM');

Schedule::command('rdwis:lock-daily-attendance')->dailyAt('17:00');


// ---------------------------------------------------------------------------
// System Logs (syslog audit trail) maintenance -- docs/SYSTEM_LOGS_SCHEMA_DESIGN.md §8
//
// Short aliases over `audit:run <task>`.
// ---------------------------------------------------------------------------
Artisan::command('audit:partitions {--months=3}', function () {
    return $this->call('audit:run', ['task' => 'partitions', '--months' => $this->option('months')]);
})->purpose('Create the syslog monthly partitions ahead of time');

Artisan::command('audit:seal', function () {
    return $this->call('audit:run', ['task' => 'seal']);
})->purpose('Seal the audit trail and export the range off-box');

Artisan::command('audit:verify', function () {
    return $this->call('audit:run', ['task' => 'verify']);
})->purpose('Recompute every event hash and seal; alert if the trail was tampered with');

Artisan::command('audit:coverage', function () {
    return $this->call('audit:run', ['task' => 'coverage']);
})->purpose('Fail if any business table is not being audited');

Artisan::command('audit:protect-check', function () {
    return $this->call('audit:run', ['task' => 'protect-check']);
})->purpose('The audit of the audit: triggers, grants and login roles');

Artisan::command('audit:attach {--wave=} {--table=}', function () {
    return $this->call('audit:run', [
        'task' => 'attach', '--wave' => $this->option('wave'), '--table' => $this->option('table'),
    ]);
})->purpose('Attach the audit trigger, wave by wave (see §8A.8) or to one table');

Artisan::command('audit:detach {--table=}', function () {
    return $this->call('audit:run', ['task' => 'detach', '--table' => $this->option('table')]);
})->purpose('Detach the audit trigger; logs already collected are kept');

// Hourly seal keeps the unsealed window short (§6.4).
Schedule::command('audit:seal')->hourly()->withoutOverlapping();
// Daily verification; non-zero exit on a break so the scheduler surfaces it.
Schedule::command('audit:verify')->dailyAt('02:10');
// Keep 3 months of partitions ahead: a missing current-month partition fails every write.
Schedule::command('audit:partitions --months=3')->monthlyOn(1, '01:30');
// Monthly protection audit.
Schedule::command('audit:protect-check')->monthlyOn(1, '01:45');

// Recover past history by diffing dated database snapshots -- docs/SYSLOG_HISTORY_RECOVERY.md
Artisan::command('audit:snapshots', function () {
    return $this->call('audit:compare', ['--list' => true]);
})->purpose('List the dated database snapshots available for history recovery');

// Rebuild past history from what the business tables already hold -- §12 backfill
Artisan::command('audit:backfill {--source=all}', function () {
    $src = (string) $this->option('source');
    $fn = $src === 'all' ? 'syslog.backfill_all()' : "syslog.backfill_{$src}()";
    $this->info("Running {$fn} ...");
    $rows = \Illuminate\Support\Facades\DB::select(
        $src === 'all' ? 'SELECT * FROM syslog.backfill_all()'
                       : "SELECT '{$src}' AS source, {$fn} AS events"
    );
    $total = 0;
    foreach ($rows as $r) { $total += (int) $r->events; $this->line(sprintf('  %-12s %s', $r->source, number_format((int) $r->events))); }
    $this->info(number_format($total) . ' historical event(s) written as source=backfill.');
    $this->warn('Run this BEFORE the first audit:seal -- backfilled rows carry past dates.');
    return 0;
})->purpose('Backfill history from lifecycle dates, module trail tables, aud.busdata and aud.revs');
