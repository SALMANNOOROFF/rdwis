<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurDecision extends Model
{
    protected $table = 'pur.purdecisions';
    protected $primaryKey = 'pdec_id';
    public $timestamps = false; // Using created_at from DB pool

    protected $fillable = [
        'pdec_pcs_id',
        'pdec_acc_id',
        'pdec_role',
        'pdec_action',
        'pdec_from_status',
        'pdec_to_status',
        'pdec_remarks',
        'pdec_amount',
        'created_at'
    ];

    /**
     * Relationship: The purchase case this decision belongs to
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'pdec_pcs_id', 'pcs_id');
    }

    /**
     * Relationship: The account that made the decision
     */
    public function account()
    {
        return $this->belongsTo(CenAccount::class, 'pdec_acc_id', 'acc_id');
    }
}
