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
