<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GenericCaseNote extends Model
{
    protected $table = 'gen.case_notes';
    protected $primaryKey = 'gcn_id';

    protected $fillable = [
        'gcn_case_id',
        'gcn_para_no',
        'gcn_title',
        'gcn_content',
        'gcn_author_id',
        'gcn_author_name',
        'gcn_author_rank',
        'gcn_author_desig',
    ];

    protected $casts = [
        'gcn_para_no' => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(GenericCase::class, 'gcn_case_id', 'gcs_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(CenAccount::class, 'gcn_author_id', 'acc_id');
    }
}

