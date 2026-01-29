<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    protected $table = 'doc.document_versions';
    protected $primaryKey = 'ver_id';
    public $timestamps = false; // Hum apna 'action_date' use kar rahe hain
    
    protected $fillable = ['doc_id', 'version_no', 'content_data', 'remarks', 'action_by', 'action_date'];

    protected $casts = [
        'content_data' => 'array', // JSON data ko automatically Array bana dega
    ];

    public function actor() {
        return $this->belongsTo(User::class, 'action_by', 'acc_id');
    }
}