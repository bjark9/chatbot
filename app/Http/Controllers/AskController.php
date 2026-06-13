<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\AiModel;
use App\Services\SimpleAskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AskController extends Controller
{
    public function __construct(private SimpleAskService $askService) {}

    /**
     * State 1: Fresh conversation screen
     */
    public function index()
    {
        return Inertia::render('ask/Index', [
            'models'        => $this->askService->getModels(),
            'selectedModel' => $this->askService::DEFAULT_MODEL,
            'conversation'  => null,
            'messages'      => [],
        ]);
    }

    /**
     * State 2: Existing conversation screen (Loaded after redirecting)
     */
    public function show(Conversation $conversation)
    {
        // Security check: ensure the conversation belongs to the logged-in user
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('ask/Index', [
            'models'        => $this->askService->getModels(),
            'selectedModel' => $this->askService::DEFAULT_MODEL,
            'conversation'  => $conversation,
            // Pass the historical messages back to populate Vue's v-for loop
            'messages'      => $conversation->messages()
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($m) => [
                    'id'       => $m->id,
                    'role'     => $m->role,
                    'content'  => $m->content,
                ]),
        ]);
    }

    /**
     * Handles incoming prompts (Both brand new and ongoing)
     */
    public function ask(Request $request, Conversation $conversation = null)
    {
        $request->validate([
            'message' => 'required|string',
            'model'   => 'required|string',
        ]);

        // If no conversation was passed in the URL parameters, create a new one
        if (!$conversation) {
            $conversation = Conversation::create([
                'user_id'     => Auth::id(),
                'title'       => substr($request->message, 0, 30) . '...', // Auto-title from first prompt
                'is_archived' => false,
            ]);
        }

        // Fetch or prepare the AI model configuration
        $aiModel = AiModel::firstOrCreate(
            ['model_id' => $request->model],
            [
                'name'       => $request->model,
                'provider'   => 'openrouter',
                'max_tokens' => 8192,
            ]
        );

        // Save the user's question to the database instantly
        Message::create([
            'conversation_id' => $conversation->id,
            'ai_model_id'     => $aiModel->id,
            'role'            => 'user',
            'content'         => $request->message,
        ]);

        // Redirect to the "show" route for this specific conversation id
        // Inertia will pick this up and seamlessly transition the frontend URL without a hard reload
        return redirect()->route('ask.show', $conversation)->with('conversation', $conversation->load('messages'));
    }
}