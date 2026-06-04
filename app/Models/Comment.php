?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'prj.comments';
    protected $primaryKey = 'cmt_id';
    public $timestamps = false;

    protected $fillable = [
        'cmt_xpgh_id', 'cmt_dtg', 'cmt_comment', 'cmt_author', 'cmt_status'
    ];

    public function history()
    {
        return $this->belongsTo(PrgHistory::class, 'cmt_xpgh_id', 'pgh_id');
    }
}