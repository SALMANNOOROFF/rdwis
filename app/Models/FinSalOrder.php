<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FinSalOrder extends Model
{
    protected $table = 'fin.salorders';
    protected $primaryKey = 'sor_id';
    public $timestamps = false;

    protected $casts = [
        'sor_id'           => 'integer',
        'sor_hed_id'       => 'integer',
        'sor_srq_id'       => 'integer',
        'sor_unt_id'       => 'integer',
        'sor_effhed_id'    => 'integer',
        'sor_effunt_id'    => 'integer',
        'sor_month'        => 'date',
        'sor_releasedtg'   => 'datetime',
        'sor_closedtg'     => 'datetime',
        'sor_netsalary'    => 'integer',
        'sor_salary'       => 'integer',
        'sor_ctrsalary'    => 'integer',
        'sor_grosalary'    => 'integer',
        'sor_arrears'      => 'integer',
        'sor_dues'         => 'integer',
        'sor_overwork'     => 'integer',
        'sor_underwork'    => 'integer',
        'sor_loaned'       => 'integer',
        'sor_withheld'     => 'integer',
        'sor_award'        => 'integer',
        'sor_penalty'      => 'integer',
        'sor_paidalready'  => 'integer',
        'sor_parent'       => 'integer',
        'sor_checked'      => 'boolean',
        'sor_noloan'       => 'boolean',
        'sor_transtype'    => 'integer',
    ];

    protected $fillable = [
        'sor_hed_id',
        'sor_releasedtg',
        'sor_status',
        'sor_remarks',
        'sor_srq_id',
        'sor_closedtg',
        'sor_unt_id',
        'sor_month',
        'sor_netsalary',
        'sor_salary',
        'sor_emp_id',
        'sor_effhed_id',
        'sor_empnamecomp',
        'sor_bnkacctitle',
        'sor_effunt_id',
        'sor_bnkaccdetail',
        'sor_ctrsalary',
        'sor_checked',
        'sor_contracts',
        'sor_noloan',
        'sor_transtype',
        'sor_sudohed',
        'sor_remarks2',
        'sor_type',
        'sor_grosalary',
        'sor_arrears',
        'sor_dues',
        'sor_overwork',
        'sor_underwork',
        'sor_loaned',
        'sor_withheld',
        'sor_award',
        'sor_penalty',
        'sor_paidalready',
        'sor_parent',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(HrSalReq::class, 'sor_srq_id', 'srq_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'sor_emp_id', 'emp_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'sor_unt_id', 'unt_id');
    }

    public function effectiveUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'sor_effunt_id', 'unt_id');
    }

    public function subheads(): HasMany
    {
        return $this->hasMany(FinSalOrderShd::class, 'sod_sor_id', 'sor_id');
    }

    public function commitment(): HasOne
    {
        return $this->hasOne(FinCommitment::class, 'cmt_docid', 'sor_id')->where('cmt_type', 'Sa');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'sor_parent', 'sor_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'sor_parent', 'sor_id');
    }
}
