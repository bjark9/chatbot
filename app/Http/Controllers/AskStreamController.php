<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\AiModel;
use App\Services\SimpleAskStreamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller pour la démonstration du streaming SSE.
 *
 * Exemple pédagogique : streaming temps réel avec Laravel + Vue.
 */
class AskStreamController extends Controller
{
    public function __construct(
        private SimpleAskStreamService $streamService
    ) {}

    /**
     * Affiche la page de streaming.
     */
    public function index(Request $request): Response
    {
        $modelId = $request->input('model')
            ?? Auth::user()?->preferred_model
            ?? SimpleAskStreamService::DEFAULT_MODEL;

        return Inertia::render('AskStream/Index', [
            'models' => $this->streamService->getModelsLight(),
            'selectedModel' => $modelId,
            'selectedModelDetails' => fn() => $this->streamService->getModelDetails($modelId),
        ]);
    }

    /**
     * Endpoint de streaming.
     */
    public function stream(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:100000',
            'model' => 'required|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'reasoning_effort' => 'nullable|string|in:low,medium,high',
            'conversation_id'  => 'required|integer|exists:conversations,id',
        ]);

        $conversation = Conversation::query()
            ->whereKey($validated['conversation_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $aiModel = AiModel::firstOrCreate(
            ['model_id' => $validated['model']],
            [
                'name' => $validated['model'],
                'provider' => 'openrouter',
                'max_tokens' => 8192,
            ]
        );

        // Update user's preferred model
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user && $user->preferred_model !== $validated['model']) {
            $user->update(['preferred_model' => $validated['model']]);
        }

        $messages = [['role' => 'user', 'content' => $validated['message']]];
        $model = $validated['model'];
        $temperature = (float) ($validated['temperature'] ?? 1.0);
        $reasoningEffort = $validated['reasoning_effort'] ?? null;
        $conversationId = (int) $validated['conversation_id'];

        return response()->stream(
            function () use ($messages, $model, $temperature, $reasoningEffort, $conversationId, $aiModel): void {
                $completeAiResponse = '';

              $this->streamService->streamToOutput(
                    $messages, 
                    $model, 
                    $temperature, 
                    $reasoningEffort,
                    function (array $event) use (&$completeAiResponse) {
                        // Re-assemble the precise stream tokens into a single text variable
                        if ($event['type'] === 'content' && !empty($event['data'])) {
                            $completeAiResponse .= $event['data'];
                        }
                        
                        if ($event['type'] === 'reasoning' && !empty($event['data'])) {
                            $completeAiResponse .= "[REASONING]" . $event['data'] . "[/REASONING]";
                        }
                    }
                );

                // Save to database
                if (!empty(trim($completeAiResponse))) {
                    $aiModel = \App\Models\AiModel::where('model_id', $model)->first();

                    \App\Models\Message::create([
                        'conversation_id' => $conversationId,
                        'ai_model_id'     => $aiModel?->id,
                        'role'            => 'assistant',
                        'content'         => $completeAiResponse,
                    ]);
                }
            },
            headers: [
                'Content-Type' => 'text/plain; charset=utf-8',
                'Cache-Control' => 'no-cache, no-store',
                'X-Accel-Buffering' => 'no',
            ]
        );
    }
}