<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentHistory extends Model
{
    protected $table = 'doc.document_history';
    protected $primaryKey = 'hist_id';
    public $timestamps = false; // Kyunki hum manually insert kar rahe hain

    protected $fillable = [
        'doc_id', 
        'from_user_id', 
        'to_user_id', 
        'action_type', 
        'notes', 
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // --- Relationships (Taake View mein Naam dikha sakein) ---
    
    public function fromUser() {
        return $this->belongsTo(User::class, 'from_user_id', 'acc_id');
    }

    public function toUser() {
        return $this->belongsTo(User::class, 'to_user_id', 'acc_id');
    }

    public function document() {
        return $this->belongsTo(Document::class, 'doc_id', 'doc_id');
    }
}