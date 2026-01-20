<?php

// App/Models/PurAttachment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurAttachment extends Model
{
    protected $table = 'pur.purattachments'; // Schema defined here
    protected $primaryKey = 'pat_id';
    public $timestamps = false; // Agar timestamps nahi hain to
}