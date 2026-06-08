<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instruction extends Model
{
    //
    protected $fillable = [
        'user_id',
        'user_instructions', // who the user is
        'assistant_instructions', // How IA should behave
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
