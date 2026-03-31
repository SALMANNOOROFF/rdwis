<?php

namespace App\Auth;

use App\Models\CenAccount;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class CenAccountUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (! isset($credentials['username'])) {
            return null;
        }

        return CenAccount::where('acc_username', $credentials['username'])
            ->where('acc_status', 'Active')
            ->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        if (! isset($credentials['password'])) {
            return false;
        }

        return password_verify($credentials['password'], $user->getAuthPassword());
    }
}
