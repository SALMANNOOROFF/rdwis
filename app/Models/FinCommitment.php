<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinCommitment extends Model
{
    protected $table = 'fin.commitments';
    protected $primaryKey = 'cmt_id';

    public $timestamps = false;

    protected $fillable = [
        'cmt_docid',
        'cmt_type',
        'cmt_date',
        'cmt_amount',
        'cmt_status',
        'cmt_effhed_id',
        'cmt_effunt_id',
        'cmt_hed_id',
        'cmt_unt_id',
        'cmt_sudohed',
        'cmt_remarks',
    ];

    protected $casts = [
        'cmt_id'        => 'integer',
        'cmt_docid'     => 'integer',
        'cmt_amount'    => 'float',
        'cmt_effhed_id' => 'integer',
        'cmt_effunt_id' => 'integer',
        'cmt_hed_id'    => 'integer',
        'cmt_unt_id'    => 'integer',
        'cmt_date'      => 'date',
    ];

    public function salOrder()
    {
        return $this->belongsTo(FinSalOrder::class, 'cmt_docid', 'sor_id');
    }

    public function effectiveHead()
    {
        return $this->belongsTo(Head::class, 'cmt_effhed_id', 'hed_id');
    }

    public function effectiveUnit()
    {
        return $this->belongsTo(Unit::class, 'cmt_effunt_id', 'unt_id');
    }

    public function head()
    {
        return $this->belongsTo(Head::class, 'cmt_hed_id', 'hed_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'cmt_unt_id', 'unt_id');
    }
}
