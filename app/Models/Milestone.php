<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use HasFactory;

    // 1. Table ka naam Schema ke sath (Bohat Zaroori hai)
    protected $table = 'prj.milestones';
    
    // 2. Primary Key - msn_idd is GENERATED ALWAYS AS IDENTITY in Postgres,
    //    so the database issues it and Eloquent must never send a value.
    protected $primaryKey = 'msn_idd';
    protected $keyType = 'int';
    public $incrementing = true;
    
    // 3. Timestamps false (Kyunki aapke DB mein created_at/updated_at nahi hain)
    public $timestamps = false;

    // 4. Wo columns jo hum Form se save karwayenge
    protected $fillable = [
        'msn_idd',
        'msn_id',
        'msn_xprj_id',  // Project ID Link
        'msn_type',     // Milestone / Activity
        'msn_desc',     // Description
        'msn_targetdt', // Target Date
        'msn_status',   // Not started / Awaited / Held up / In progress / Completed
        'msn_cost', 
        'msn_startdt', 
        'msn_achvdt', 
        'msn_comp', 
        'msn_rem',
        'msn_cc_path'
    ];

    // 5. Project ke sath Linkage
    public function project()
    {
        return $this->belongsTo(Project::class, 'msn_xprj_id', 'prj_id');
    }
}