<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory, \App\Traits\HorizonScoped;

    protected $table = 'prj.projects'; // Schema ke sath
    protected $primaryKey = 'prj_id';
    public $timestamps = false; 

    public function getHorizonColumn()
    {
        return 'prj_unt_id';
    }

    /**
     * A project is raised by SORD and stays a "Draft" until SORD publishes it.
     * Until then the owning division must not see it anywhere in the system,
     * so drafts are filtered out for everyone except SORD / Super Admin.
     *
     * Escape hatch: Project::withoutGlobalScope('published')
     */
    protected static function booted(): void
    {
        static::addGlobalScope('published', function ($query) {
            if (! \Illuminate\Support\Facades\Auth::check()) {
                return; // console commands, jobs, migrations
            }

            $context = \App\Services\Auth\UserAccessContext::forUser(\Illuminate\Support\Facades\Auth::user());
            if ($context->isSuperAdmin() || $context->isSord()) {
                return;
            }

            $query->where(function ($q) {
                $q->whereNull('prj_status')
                  ->orWhereRaw("LOWER(TRIM(prj_status)) <> 'draft'");
            });
        });
    } 

    protected $fillable = [
        'prj_title',
        'prj_cost',
        'prj_startdt',
        'prj_enddt',
        'prj_estenddt',
        'prj_rcptdt',
        'prj_assigndt',
        'prj_propdt',
        'prj_aprvdt',
        'prj_unt_id',
        'prj_scope',
        'prj_sponsor',
        'prj_status',
        'prj_reporting',
        'prj_code',
        'prj_propcost',
        'prj_aprvcost',
        'prj_rem',
        'prj_notes'
    ];

    // Relationships


    // Relation: Project belongs to a Unit
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'prj_unt_id', 'unt_id');
    }
    
    public function milestones()
    {
        return $this->hasMany(Milestone::class, 'msn_xprj_id', 'prj_id')->orderBy('msn_id', 'asc');
    }

    public function history()
    {
        return $this->hasMany(PrgHistory::class, 'pgh_xprj_id', 'prj_id');
    }

    // Relationship: Project has many Attachments
    public function attachments()
    {
        return $this->hasMany(PrjAttachment::class, 'jat_objid', 'prj_id')
                    ->whereIn('jat_objtype', ['prj', 'Project']);
    }


    // --- NEW RELATIONSHIP (Jo Missing Thi) ---
    public function document() {
        // Project ka ek Document (MPR) hota hai
        return $this->hasOne(Document::class, 'prj_id', 'prj_id');
    }
}
