<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'cen.accounts';
    protected $primaryKey = 'acc_id';
    public $timestamps = false; 

    protected $fillable = [
        'acc_username', 'acc_pass', 'acc_unt_id', 'acc_desig', 'acc_level',
    ];

    protected $hidden = ['acc_pass'];

    public function getAuthPassword()
    {
        return $this->acc_pass;
    }

    // --- RELATIONSHIPS ---
    public function role()
    {
        return $this->belongsTo(Role::class, 'acc_desig', 'rol_desig');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'acc_unt_id', 'unt_id');
    }

    // --- ROLE LOGIC (DEBUGGED) ---

    public function isSORD()
    {
        // 1. DIRECT USERNAME CHECK (Sabse Pehle Ye Check Hoga)
        if (trim($this->acc_username) == 'nislam2') {
            return true;
        }

        if (!$this->unit) {
            return false;
        }

        // 2. Unit Area Check ('rdwprj' database se aa raha hai)
        $area = strtolower(trim($this->unit->unt_area));
        if (in_array($area, ['rdwprj', 'prjrdw', 'rdw'])) {
            return true;
        }

        return false;
    }

    public function isDivision()
    {
        // Agar SORD hai to Division nahi ho sakta
        if ($this->isSORD()) {
            return false;
        }
        
        // Agar Unit assign nahi hai to false
        if (!$this->unit) {
            return false;
        }

        // Division Check (Default 'prj')
        $area = strtolower(trim($this->unit->unt_area));
        return $area == 'prj';
    }
}