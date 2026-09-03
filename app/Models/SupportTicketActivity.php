<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class SupportTicketActivity extends Model
{
    protected $table = 'sup.ticket_activities';
    protected $primaryKey = 'act_id';

    public $timestamps = false;

    protected $fillable = [
        'act_tkt_id',
        'act_user_id',
        'act_user_name',
        'act_user_role',
        'act_action',
        'act_message',
        'act_attachment',
        'act_created_at',
    ];

    protected $casts = [
        'act_created_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'act_tkt_id', 'tkt_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'act_user_id', 'acc_id');
    }
}
