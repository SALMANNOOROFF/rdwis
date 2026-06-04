<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrgHistory extends Model
{
    use HasFactory;

    protected $table = 'prj.prghistory';
    protected $primaryKey = 'pgh_id';
    public $timestamps = false;

    // SIRF DB COLUMNS (Schema ke mutabiq)
    protected $fillable = [
        'pgh_xprj_id',
        'pgh_dtg',
        'pgh_progress',
        'pgh_author', // New: Required in DB
        'pgh_status', // New: Required in DB
        'pgh_level',  // New: Required in DB
        'pgh_underedit'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'pgh_xprj_id', 'prj_id');
    }
}