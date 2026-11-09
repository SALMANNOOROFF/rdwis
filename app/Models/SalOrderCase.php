<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalOrderCase extends Model
{
    protected $table = 'fin.sal_order_cases';
    protected $primaryKey = 'soc_id';

    protected $fillable = [
        'soc_month',
        'soc_minute_no',
        'soc_title',
        'soc_encl_ref',
        'soc_para1_text',
        'soc_para2_text',
        'soc_para3_text',
        'soc_payment_channel',
        'soc_total_due_emps',
        'soc_total_paid_emps',
        'soc_split_salaries_count',
        'soc_split_payments_count',
        'soc_total_payments',
        'soc_total_amount',
        'soc_current_stage',
        'soc_current_acc_id',
        'soc_current_holder',
        'soc_status',
        'soc_created_by',
        'soc_created_name',
        'soc_created_desig',
        'soc_table_data',
    ];

    protected $casts = [
        'soc_total_amount' => 'decimal:2',
        'soc_total_due_emps' => 'integer',
        'soc_total_paid_emps' => 'integer',
        'soc_split_salaries_count' => 'integer',
        'soc_split_payments_count' => 'integer',
        'soc_total_payments' => 'integer',
        'soc_table_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function decisions(): HasMany
    {
        return $this->hasMany(SalOrderCaseDecision::class, 'scd_soc_id', 'soc_id')->orderBy('scd_dtg', 'desc');
    }

    public function decisionsAsc(): HasMany
    {
        return $this->hasMany(SalOrderCaseDecision::class, 'scd_soc_id', 'soc_id')->orderBy('scd_dtg', 'asc');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(FinSalOrder::class, 'sor_soc_id', 'soc_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(CenAccount::class, 'soc_created_by', 'acc_id');
    }

    public function currentHolder(): BelongsTo
    {
        return $this->belongsTo(CenAccount::class, 'soc_current_acc_id', 'acc_id');
    }
}
