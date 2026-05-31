<?php

namespace App\Http\Controllers;

use App\Services\SimpleAskService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AskController extends Controller
{
    public function __construct(private SimpleAskService $askService) {}

    /**
     * Affiche la liste des models
     */
    public function index()
    {
        return Inertia::render('ask/Index', [
            'models' => $this->askService->getModels(),
            'selectedModel' => $this->askService::DEFAULT_MODEL,
        ]);
    }

    /**
     * Envoie une question au modèle IA et retourne la réponse
     * @param \Illuminate\Http\Request $request
     * @param string $request->message      Le message à envoyer au modèle
     * @param string $request->model        L'identifiant du modèle IA à utiliser
     * @return \Inertia\Response
     */
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'model' => 'required|string',
        ]);

        $response = null;
        $error = null;
        $messages = [[
            'role' => 'user',
            'content' => $request->message,
        ]];

        try {
            $response = $this->askService->sendMessage(
                messages: $messages,
                model: $request->model
            );
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return Inertia::render('ask/Index', [
            'models' => $this->askService->getModels(),
            'selectedModel' => $request->model,
            'message' => $request->message,
            'response' => $response,
            'error' => $error,
        ]);
    }
}