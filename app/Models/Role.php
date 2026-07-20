<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'cen.roles'; // Schema ke sath table name
    protected $primaryKey = 'rol_desig'; // PK string hai
    public $incrementing = false; // Auto-increment nahi hai
    protected $keyType = 'string'; // PK type string hai
    public $timestamps = false;

    protected $fillable = [
        'rol_unt_id',
        'rol_level',
        'rol_desig',
        'rol_desigshort',
        'rol_desigtype',
        'rol_reportlevel',
        'rol_access',
        'rol_auth',
    ];
}
