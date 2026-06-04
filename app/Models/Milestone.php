<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use HasFactory;

    // 1. Table ka naam Schema ke sath (Bohat Zaroori hai)
    protected $table = 'prj.milestones';
    
    // 2. Primary Key
    protected $primaryKey = 'msn_id';
    
    // 3. Timestamps false (Kyunki aapke DB mein created_at/updated_at nahi hain)
    public $timestamps = false;

    // 4. Wo columns jo hum Form se save karwayenge
    protected $fillable = [
        'msn_xprj_id',  // Project ID Link
        'msn_type',     // Physical / Financial
        'msn_desc',     // Description
        'msn_targetdt', // Target Date
        'msn_status',   // Pending / Completed
        
        // Niche waly columns optional hain (Agar DB mein hain to theek, warna error de sakte hain)
        'msn_cost', 
        'msn_startdt', 
        'msn_achvdt', 
        'msn_comp', 
        'msn_rem'
    ];

    // 5. Project ke sath Linkage
    public function project()
    {
        return $this->belongsTo(Project::class, 'msn_xprj_id', 'prj_id');
    }
}