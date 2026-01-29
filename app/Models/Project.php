<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'prj.projects'; // Schema ke sath
    protected $primaryKey = 'prj_id';
    public $timestamps = false; 

    protected $fillable = [
        'prj_title',
        'prj_cost',
        'prj_startdt',
        'prj_enddt',
        'prj_unt_id',
    
        'prj_title',  'prj_scope', 'prj_sponsor', 'prj_status',
         'prj_reporting', 'prj_code', 'prj_propcost', 'prj_aprvcost'
    ];

    // Relationships


    // Relation: Project belongs to a Unit
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'prj_unt_id', 'unt_id');
    }
    
    public function milestones()
    {
        return $this->hasMany(Milestone::class, 'msn_xprj_id', 'prj_id');
    }

    public function history()
    {
        return $this->hasMany(PrgHistory::class, 'pgh_xprj_id', 'prj_id');
    }

    // Relationship: Project has many Attachments
    public function attachments()
    {
        return $this->hasMany(PrjAttachment::class, 'jat_objid', 'prj_id')
                    ->where('jat_objtype', 'Project');
    }


    // --- NEW RELATIONSHIP (Jo Missing Thi) ---
    public function document() {
        // Project ka ek Document (MPR) hota hai
        return $this->hasOne(Document::class, 'prj_id', 'prj_id');
    }
}
