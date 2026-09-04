<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuickRemark extends Model
{
    use HasFactory;

    protected $table = 'cen.user_quick_remarks';
    protected $primaryKey = 'uqr_id';
    public $timestamps = true;

    protected $fillable = [
        'uqr_acc_id',
        'uqr_label',
        'uqr_description',
        'uqr_order',
    ];

    /**
     * Relationship to User (Account)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'uqr_acc_id', 'acc_id');
    }

    /**
     * Scope: remarks for specific user
     */
    public function scopeForUser($query, $accId)
    {
        return $query->where('uqr_acc_id', $accId)->orderBy('uqr_order', 'asc')->orderBy('uqr_id', 'asc');
    }

    /**
     * Word count helper
     */
    public static function countWords(?string $text): int
    {
        if (empty($text)) {
            return 0;
        }
        return count(preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY));
    }
}
