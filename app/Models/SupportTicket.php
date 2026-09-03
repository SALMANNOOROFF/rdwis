<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\Unit;

class SupportTicket extends Model
{
    protected $table = 'sup.tickets';
    protected $primaryKey = 'tkt_id';

    const CREATED_AT = 'tkt_created_at';
    const UPDATED_AT = 'tkt_updated_at';

    protected $fillable = [
        'tkt_ref',
        'tkt_type',
        'tkt_module',
        'tkt_subject',
        'tkt_description',
        'tkt_priority',
        'tkt_status',
        'tkt_user_id',
        'tkt_user_name',
        'tkt_user_role',
        'tkt_unt_id',
        'tkt_unt_name',
        'tkt_is_apex',
        'tkt_attachment',
        'tkt_solved_by',
        'tkt_solved_by_name',
        'tkt_solved_at',
        'tkt_resolution_note',
    ];

    protected $casts = [
        'tkt_is_apex'   => 'boolean',
        'tkt_solved_at' => 'datetime',
        'tkt_created_at'=> 'datetime',
        'tkt_updated_at'=> 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tkt_user_id', 'acc_id');
    }

    public function solver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tkt_solved_by', 'acc_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'tkt_unt_id', 'unt_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(SupportTicketActivity::class, 'act_tkt_id', 'tkt_id')
            ->orderBy('act_created_at', 'asc');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('tkt_status', ['Open', 'In Progress', 'Returned']);
    }

    public function scopeResolved($query)
    {
        return $query->whereIn('tkt_status', ['Resolved', 'Rejected', 'Closed']);
    }

    public function scopeApexDirectives($query)
    {
        return $query->where('tkt_is_apex', true);
    }
}
