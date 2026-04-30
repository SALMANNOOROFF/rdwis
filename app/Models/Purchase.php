<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use \App\Traits\HorizonScoped;

    // Table and primary key
    protected $table = 'pur.purcases';
    protected $primaryKey = 'pcs_id';
    public $timestamps = false;

    public function getHorizonColumn()
    {
        return 'pcs_unt_id';
    }

    // Fillable fields for mass assignment
 protected $fillable = [
    'pcs_title',
    'pcs_date',
    'pcs_status',
    'pcs_type',
    'pcs_unt_id',
    'pcs_hed_id',
    'pcs_effhed_id',
    'pcs_effunt_id',
    'pcs_price',
    'pcs_remarks',
    'pcs_subject',
    'pcs_minute'
];


    /**
     * Relationship: Purchase belongs to a Project (Head)
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'pcs_hed_id', 'prj_id');
    }

    /**
     * Purchase items
     */
    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'pci_pcs_id', 'pcs_id');
    }

    /**
     * Purchase quotes
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class, 'qte_pcs_id', 'pcs_id');
    }

    /**
     * Purchase no-quote records
     */
    public function noQuotes()
    {
        return $this->hasMany(NoQuote::class, 'nqt_pcs_id', 'pcs_id');
    }

    /**
     * Purchase attachments
     */
    public function attachments()
    {
        return $this->hasMany(PurAttachment::class, 'pat_objid', 'pcs_id');
    }

    /**
     * Approval trail decisions
     */
    public function decisions()
    {
        return $this->hasMany(PurDecision::class, 'pdec_pcs_id', 'pcs_id')->orderBy('pdec_id', 'desc');
    }

    /**
     * The single most recent decision for quick status context
    */
    public function latestDecision()
    {
        return $this->hasOne(PurDecision::class, 'pdec_pcs_id', 'pcs_id')->latestOfMany('pdec_id');
    }

    /**
     * Purchase belongs to a Unit (Division)
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'pcs_unt_id', 'unt_id');
    }

    /**
     * Purchase notifications
     */
    public function notifications()
    {
        return $this->hasMany(PurNotification::class, 'pnt_pcs_id', 'pcs_id');
    }
}

