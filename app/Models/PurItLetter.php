<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurItLetter extends Model
{
    protected $table = 'pur.pur_it_letters';
    protected $primaryKey = 'pit_id';
    public $timestamps = true;

    protected $fillable = [
        'pit_pcs_id',
        'pit_refno',
        'pit_date',
        'pit_subject',
        'pit_para1',
        'pit_para2',
        'pit_para3',
        'pit_signatory_name',
        'pit_signatory_rank',
        'pit_signatory_dept',
        'pit_distribution_label',
        'pit_paragraphs',
        'pit_firms',
        'pit_items',
    ];

    protected $casts = [
        'pit_paragraphs' => 'array',
        'pit_firms'      => 'array',
        'pit_items'      => 'array',
    ];

    /**
     * Relationship: PurItLetter belongs to a Purchase Case
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'pit_pcs_id', 'pcs_id');
    }
}
