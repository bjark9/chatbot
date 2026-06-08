<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'is_archived',
    ];

    public function updateAutoTitle(): self
    {
        if ($this->title && $this->title !== 'New Conversation') {
            return $this;
        }

        // Check if it's the first message else skip
        $firstUserMessage = $this->messages()
            ->where('role', 'user')
            ->orderBy('created_at')
            ->first();

        if (! $firstUserMessage) {
            return $this;
        }

        $title = trim($firstUserMessage->content);
        $title = preg_replace('/\s+/', ' ', $title);
        $title = Str::limit($title, 60, '...');

        $this->title = $title;
        $this->save();

        return $this;
    }

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