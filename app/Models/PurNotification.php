<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurNotification extends Model
{
    protected $table = 'pur.purnotifications';
    protected $primaryKey = 'pnt_id';
    public $timestamps = false; // Using created_at from DB pool

    protected $fillable = [
        'pnt_acc_id',
        'pnt_pcs_id',
        'pnt_message',
        'pnt_is_read',
        'created_at'
    ];

    /**
     * Relationship: Recipient account
     */
    public function recipient()
    {
        return $this->belongsTo(CenAccount::class, 'pnt_acc_id', 'acc_id');
    }

    /**
     * Relationship: Related purchase case
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'pnt_pcs_id', 'pcs_id');
    }
}
