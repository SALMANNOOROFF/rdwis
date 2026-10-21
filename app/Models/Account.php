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

    public function isDlog(): bool
    {
        $desig = strtoupper(trim((string) ($this->acc_desig ?? '')));
        $auth = strtolower(trim((string) ($this->acc_auth ?? '')));
        $username = strtolower(trim((string) ($this->acc_username ?? '')));
        $desigShort = strtoupper(trim((string) ($this->acc_desigshort ?? '')));
        $area = strtolower(trim((string) ($this->acc_untarea ?? '')));

        if (
            str_contains($desig, 'DIRECTOR LOGISTICS') ||
            str_contains($desig, 'DIRECTOR LOG') ||
            in_array($desigShort, ['DLOG', 'DIR LOG', 'D LOG', 'DIR LOGISTICS'], true) ||
            preg_match('/\b(DLOG)\b/i', $desig) ||
            in_array($auth, ['dlog', 'log'], true) ||
            in_array($area, ['log', 'dlog', 'logistics'], true) ||
            in_array($username, ['dlog', 'logistics'], true)
        ) {
            return true;
        }

        return false;
    }

    public function isMdDdgDg(): bool
    {
        if ($this->isDlog()) {
            return true;
        }

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