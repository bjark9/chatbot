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
    // API Service
    public function __construct(private SimpleAskService $askService) {}

    /**
     * Envoie les valeurs des variables 'models' et 'selectedModel' a la vue ask/Index.vue 
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('ask/Index', [
            'models' => $this->askService->getModels(),
            'selectedModel' => $this->askService::DEFAULT_MODEL,
        ]);
    }

    /**
     * Create a new message for each request to the API
     * @param \Illuminate\Http\Request $request
     * @param string $request->message      Le message à envoyer au modèle
     * @param string $request->model        L'identifiant du modèle IA à utiliser
     * @return \Inertia\Response
     */
    public function ask(Request $request)
    {
        // Validate that the request contains exactly two variabels: a message (string) and a model (string)
        $request->validate([
            'message' => 'required|string',
            'model' => 'required|string',
        ]);

        $conversation = Conversation::create([
            'user_id' => Auth::id(),
            'title' => 'New Conversation',
            'is_archived' => false,
        ]);

        // Look up or create an AiModel matching request->model
        $aiModel = AiModel::firstOrCreate(
            ['model_id' => $request->model],
            [
                'name' => $request->model,
                'provider' => 'openrouter',
                'max_tokens' => 8192,
            ]
        );

        // Save user's message
        Message::create([
            'conversation_id' => $conversation->id,
            'ai_model_id' => $aiModel->id,
            'role' => 'user',
            'content' => $request->message,
        ]);

        // Create the chat history and format as role/content
        $history = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn(Message $m) => [
                'role' => $m->role,
                'content' => $m->content,
            ])
            ->toArray();

        // Call the SimpleAskService
        try {
            $response = $this->askService->sendMessage(
                messages: $history,
                model: $request->model
            );

            // Create AI message
            Message::create([
                'conversation_id' => $conversation->id,
                'ai_model_id' => $aiModel->id,
                'role' => 'assistant',
                'content' => $response,
                'is_error' => false,
            ]);

            $conversation->updateAutoTitle();
        } catch (\Exception $e) {
            $response = null;

            Message::create([
                'conversation_id' => $conversation->id,
                'ai_model_id' => $aiModel->id,
                'role' => 'assistant',
                'content' => $e->getMessage(),
                'is_error' => true,
            ]);

            $conversation->updateAutoTitle();
        }

        return Inertia::render('ask/Index', [
            'models' => $this->askService->getModels(),
            'selectedModel' => $request->model,
            'conversation' => $conversation->load('messages.ai_model'),
        ]);
    }
}