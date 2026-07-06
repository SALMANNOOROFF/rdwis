<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'cen.accounts';
    protected $primaryKey = 'acc_id';
    public $timestamps = false;
    protected $rememberTokenName = null;

    protected $fillable = [
        'acc_username', 'acc_pass', 'acc_unt_id', 'acc_desig', 'acc_level',
    ];

    protected $hidden = ['acc_pass'];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'acc_unt_id', 'unt_id');
    }
}