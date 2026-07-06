<?php
// App/Models/PurAttachment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurAttachment extends Model
{
    protected $table = 'pur.purattachments'; // Schema defined here
    protected $primaryKey = 'pat_id';
    public $timestamps = false; // Agar timestamps nahi hain to

    protected $fillable = [
        'pat_objtype',
        'pat_objid',
        'pat_type',
        'pat_path'
    ];

    public function getPatFilenameAttribute()
    {
        $path = (string) ($this->pat_path ?? '');
        $path = str_replace('\\', '/', $path);
        return basename($path);
    }
}
