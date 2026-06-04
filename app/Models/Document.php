<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'doc.documents';
    protected $primaryKey = 'doc_id';
    protected $fillable = ['prj_id', 'doc_type', 'doc_ref_no', 'current_owner_id', 'creator_id', 'status'];

    public function project() {
        return $this->belongsTo(Project::class, 'prj_id', 'prj_id');
    }

    public function currentOwner() {
        return $this->belongsTo(User::class, 'current_owner_id', 'acc_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'creator_id', 'acc_id');
    }

    public function versions() {
        return $this->hasMany(DocumentVersion::class, 'doc_id', 'doc_id')->orderBy('ver_id', 'desc');
    }

    public function latestVersion() {
        return $this->hasOne(DocumentVersion::class, 'doc_id', 'doc_id')->latestOfMany('ver_id');
    }

    // --- YE FUNCTION MISSING THA, ISAY ADD KAREIN ---
    public function history() {
        return $this->hasMany(DocumentHistory::class, 'doc_id', 'doc_id')->orderBy('created_at', 'desc');
    }
}