<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrBnkAccount extends Model
{
    protected $table = 'hr.bnkaccounts';
    protected $primaryKey = 'bac_id';
    public $timestamps = false;

    protected $fillable = [
        'bac_emp_id',
        'bac_bnkname',
        'bac_bchname',
        'bac_bchcode',
        'bac_acctitle',
        'bac_accnum',
        'bac_bchcity',
        'bac_selforpay',
    ];
}
