<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'is_archived',
    ];

    // A conversation has ONE or MANY messages
    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    // A conversation belongs to ONE user 
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}