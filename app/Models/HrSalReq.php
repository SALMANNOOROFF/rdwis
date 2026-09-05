<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HrSalReq extends Model
{
    protected $table = 'hr.salreqs';
    protected $primaryKey = 'srq_id';
    public $timestamps = false;

    protected $casts = [
        'srq_id'           => 'integer',
        'srq_unt_id'       => 'integer',
        'srq_hed_id'       => 'integer',
        'srq_effhed_id'    => 'integer',
        'srq_effunt_id'    => 'integer',
        'srq_salary'       => 'integer',
        'srq_ctrsalary'    => 'integer',
        'srq_grosalary'    => 'integer',
        'srq_netsalary'    => 'integer',
        'srq_fulfilment'   => 'integer',
        'srq_parent'       => 'integer',
        'srq_month'        => 'date',
        'srq_releasedtg'   => 'datetime',
        'srq_closedtg'     => 'datetime',
        'srq_unpaiddays'   => 'float',
        'srq_paidholidays' => 'float',
        'srq_underwork'    => 'float',
        'srq_overwork'     => 'float',
        'srq_award'        => 'float',
        'srq_penalty'      => 'float',
        'srq_loaned'       => 'float',
        'srq_withheld'     => 'float',
        'srq_arrears'      => 'float',
        'srq_dues'         => 'float',
        'srq_paidalready'  => 'float',
    ];

    protected $fillable = [
        'srq_emp_id',
        'srq_unt_id',
        'srq_hed_id',
        'srq_effhed_id',
        'srq_effunt_id',
        'srq_month',
        'srq_status',
        'srq_salary',
        'srq_empnamecomp',
        'srq_ctrsalary',
        'srq_grosalary',
        'srq_netsalary',
        'srq_contracts',
        'srq_bnkaccdetail',
        'srq_bnkacctitle',
        'srq_remarks',
        'srq_unpaiddays',
        'srq_paidholidays',
        'srq_underwork',
        'srq_overwork',
        'srq_award',
        'srq_penalty',
        'srq_loaned',
        'srq_withheld',
        'srq_arrears',
        'srq_dues',
        'srq_paidalready',
        'srq_sudohed',
        'srq_fulfilment',
        'srq_releasedtg',
        'srq_closedtg',
        'srq_parent',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'srq_emp_id', 'emp_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'srq_unt_id', 'unt_id');
    }

    public function effectiveUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'srq_effunt_id', 'unt_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'srq_parent', 'srq_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'srq_parent', 'srq_id');
    }

    public function order(): HasOne
    {
        return $this->hasOne(FinSalOrder::class, 'sor_srq_id', 'srq_id');
    }
}
