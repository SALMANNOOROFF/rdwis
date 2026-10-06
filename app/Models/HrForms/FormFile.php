<?php

namespace App\Models\HrForms;

use Illuminate\Database\Eloquent\Model;

class FormFile extends Model
{
    protected $table = 'hrforms.form_files';

    protected $fillable = [
        'case_form_id',
        'version',
        'file_path',
        'file_name',
        'checksum',
        'byte_size',
        'is_final',
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'is_final'      => 'boolean',
        'version'       => 'integer',
        'byte_size'     => 'integer',
        'generated_at'  => 'datetime',
    ];

    public function form()
    {
        return $this->belongsTo(CaseForm::class, 'case_form_id');
    }
}
