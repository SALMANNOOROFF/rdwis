<?php

namespace App\Models\HrForms;

use Illuminate\Database\Eloquent\Model;

class FormTemplate extends Model
{
    protected $table = 'hrforms.form_templates';
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function matrixRules()
    {
        return $this->hasMany(FormMatrix::class, 'form_code', 'form_code');
    }

    public function approvalChains()
    {
        return $this->hasMany(ApprovalChain::class, 'form_code', 'form_code')->orderBy('sequence');
    }
}
