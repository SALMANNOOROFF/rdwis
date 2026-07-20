<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'cen.accounts';
    protected $primaryKey = 'acc_id';

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'acc_unt_id', 'unt_id');
    }
}