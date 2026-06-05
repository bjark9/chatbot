<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiModel extends Model
{
    use HasFactory;

    protected $table = 'ai_models'; // is needed because Laravel would otherwise guess the table name as a_i_models from the class name AiModel.

    protected $fillable = [
        'model_id',
        'name',
        'provider',
        'max_tokens',
    ];

    // One AI model can be used by many messages
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}