<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Conversation;
use App\Models\User;

class ConversationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the test user by email
        $user = User::where('email', 'test@example.com')->first();

        // Create 10 conversations for that user
        Conversation::factory(25)->create([
            'user_id' => $user->id,
        ]);


        Conversation::factory(10)->create(); // Create 10 fake conversations using the Conversation factory
    }
}
