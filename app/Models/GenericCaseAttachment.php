<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GenericCaseAttachment extends Model
{
    protected $table = 'gen.case_attachments';
    protected $primaryKey = 'gca_id';
    public $timestamps = false;

    protected $fillable = [
        'gca_case_id',
        'gca_title',
        'gca_filename',
        'gca_path',
        'gca_size',
        'gca_mime',
        'gca_uploader_id',
        'created_at',
    ];

    protected $casts = [
        'gca_size'   => 'integer',
        'created_at' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(GenericCase::class, 'gca_case_id', 'gcs_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(CenAccount::class, 'gca_uploader_id', 'acc_id');
    }
}

