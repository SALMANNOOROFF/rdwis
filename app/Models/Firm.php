<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Firm extends Model
{
    // Table name with schema
    protected $table = 'frm.firmz';

    // Primary key
    protected $primaryKey = 'frm_id';

    // If your table does NOT have created_at and updated_at
    public $timestamps = false;

    // Mass assignable fields (adjust according to your table)
    protected $fillable = [
        'frm_name',
        'frm_address',
        'frm_contact',
        // add other columns here
    ];

    /**
     * Optional: Define relationship to quotes
     */
    public function quotes()
    {
        // A firm can have many quotes
        return $this->hasMany(Quote::class, 'qte_frm_id', 'frm_id');
    }
}
