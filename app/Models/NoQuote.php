<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoQuote extends Model
{
    protected $table = 'pur.noquotes';
    protected $primaryKey = 'nqt_id';
    public $timestamps = false;

    protected $fillable = [
        'nqt_pcs_id',
        'nqt_firmname',
        'nqt_reason',
        'nqt_date'
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'nqt_pcs_id', 'pcs_id');
    }
}
