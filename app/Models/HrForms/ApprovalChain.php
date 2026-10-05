<?php

namespace App\Models\HrForms;

use Illuminate\Database\Eloquent\Model;

class ApprovalChain extends Model
{
    protected $table = 'hrforms.approval_chains';
    protected $guarded = [];

    protected $casts = [
        'sequence' => 'integer',
        'can_approve' => 'boolean',
        'is_final_authority' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(FormTemplate::class, 'form_code', 'form_code');
    }
}
