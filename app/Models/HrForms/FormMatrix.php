<?php

namespace App\Models\HrForms;

use Illuminate\Database\Eloquent\Model;

class FormMatrix extends Model
{
    protected $table = 'hrforms.form_matrix';
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'default_order' => 'integer',
    ];

    public function template()
    {
        return $this->belongsTo(FormTemplate::class, 'form_code', 'form_code');
    }
}
