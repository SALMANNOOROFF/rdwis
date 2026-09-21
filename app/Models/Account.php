<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'cen.accounts';
    protected $primaryKey = 'acc_id';
    public $timestamps = false;
    protected $rememberTokenName = null;

    protected $fillable = [
        'acc_username', 'acc_pass', 'acc_unt_id', 'acc_desig', 'acc_level',
    ];

    protected $hidden = ['acc_pass'];

    public function isMdDdgDg(): bool
    {
        $desig = strtoupper(trim((string) ($this->acc_desig ?? '')));
        $auth = strtolower(trim((string) ($this->acc_auth ?? '')));
        $username = strtolower(trim((string) ($this->acc_username ?? '')));
        $desigShort = strtoupper(trim((string) ($this->acc_desigshort ?? '')));

        if (in_array($auth, ['md', 'ddg', 'dg'], true) || in_array($username, ['md', 'ddg', 'dg'], true)) {
            return true;
        }

        if (str_contains($desig, 'MANAGING DIRECTOR') || str_contains($desig, 'DEPUTY DIRECTOR GENERAL') || str_contains($desig, 'DIRECTOR GENERAL')) {
            return true;
        }

        if (in_array($desigShort, ['MD', 'DDG', 'DG', 'DDG NRDI'], true)) {
            return true;
        }

        return false;
    }
}