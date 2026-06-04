<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Message;
use App\Models\Conversation;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // For each existing conversation, create 5 messages
        Conversation::all()->each(function ($conversation) {
            Message::factory(5)->create([
                'conversation_id' => $conversation->id, // override with real conversation
            ]);
        });
    }
}
