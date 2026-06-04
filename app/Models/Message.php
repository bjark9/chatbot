<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id', //FK
        'ai_model_id', // FK
        'role', //user or AI
        'content', //text
        'tokens_used', //int
        'is_error', //bool, flag failed responses
    ];

    // A conversation has multiple messages
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // A message belongs to a specific AI model
    public function ai_model()
    {
        return $this->belongsTo(AiModel::class);
    }
}

?>