<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\SimpleAskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function __construct(private SimpleAskService $askService) {}

    /**
     * Show a conversation and its messages
     */
    public function index(Conversation $conversation)
    {
       return Inertia::render('ask/Index', [
            'conversation' => $conversation->load('messages.ai_model'),
            'models'       => $this->askService->getModels(),
       ]);
    }

    public function store(Request $request, Conversation $conversation)
    {
        $request->validate([
            'message'=>'required|string|max:10000',
            'model'=>'required|string',
        ]);

        // Find or create the aiModel record
        $aiModel = AiModel::firstOrCreate(
            ['model_id' => $request->input('model')],
            [
                'name' => $request->input('model'),
                'provider' => 'openrouter',
                'max_tokens' => 8192,
            ]
        );

        // Save the users message
        Message::create([
            'conversation_id' => $conversation->id,
            'ai_model_id'     => $aiModel->id,
            'role'            => 'user',
            'content'         => $request->input('message'),
        ]);

        // Build the history to send to the API (all messages in order)
        $history = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn(Message $m) => [
                'role'    => $m->role,
                'content' => $m->content,
            ])
            ->toArray();

        // Call the API
        try {
            $responseText = $this->askService->sendMessage($history, $request->input('model'));

            Message::create([
                'conversation_id' => $conversation->id,
                'ai_model_id'     => $aiModel->id,
                'role'            => 'assistant',
                'content'         => $responseText,
                'is_error'        => false,
            ]);

            $conversation->updateAutoTitle();
        } catch (\RuntimeException $e) {
            Message::create([
                'conversation_id' => $conversation->id,
                'ai_model_id'     => $aiModel->id,
                'role'            => 'assistant',
                'content'         => $e->getMessage(),
                'is_error'        => true,
            ]);

            $conversation->updateAutoTitle();
        }

        return redirect()->route('ask.conversation', ['conversation' => $conversation->id]);
    }
}
