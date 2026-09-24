<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinContractVerif extends Model
{
    protected $table = 'fin.contractsverif';
    protected $primaryKey = 'cvf_ctr_id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'cvf_ctr_id',
        'cvf_verif',
        'cvf_dtg',
    ];

    protected $casts = [
        'cvf_verif' => 'boolean',
        'cvf_dtg'   => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(HrContract::class, 'cvf_ctr_id', 'ctr_id');
    }
}
