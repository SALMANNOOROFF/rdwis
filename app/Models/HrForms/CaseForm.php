<?php

namespace App\Models\HrForms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseForm extends Model
{
    use SoftDeletes;

    protected $table = 'hrforms.case_forms';
    protected $guarded = [];

    protected $casts = [
        'case_id' => 'integer',
        'is_required' => 'boolean',
        'form_data' => 'array',
        'snapshot_data' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(FormTemplate::class, 'form_code', 'form_code');
    }

    public function case()
    {
        return $this->belongsTo(\App\Models\HrCtrCase::class, 'case_id', 'ctc_id');
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'Submitted' || !empty($this->submitted_at);
    }

    public function isDraft(): bool
    {
        return $this->status === 'Draft';
    }

    public function isPendingInput(): bool
    {
        return $this->status === 'Pending Input';
    }

    public function isReady(): bool
    {
        return $this->status === 'Ready';
    }

    public function isPendingRemoval(): bool
    {
        return $this->status === 'Pending Removal';
    }
}
