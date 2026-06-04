<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConversationController extends Controller
{
    /**
     * GET the conversations and pass them as a prop to the corresponding vue using Inertia 
     * @return Inertia\Response 
     */
    public function index()
    {
        $conversations = Conversation::where('user_id', Auth::id())
            ->where('is_archived',false)
            ->orderBy('updated_at', 'desc')
            ->get();

        return Inertia::render('ask/Conversations', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Create a new conversation
     */
    public function store(Request $request)
    {
        // Here we validate if 'title' variable in the request is conform to following rules:
        $request->validate(['title' => 'nullable|string|max:255']); 

        $conversation = Conversation::create(
            [
                'user_id' => Auth::id(),
                'title' => $request->input('title', 'New Conversation'),
                'is_archived' => false,
            ]
        );
        return response()->json($conversation);
    }

    /**
     * GET conversation/{id}
     * Get a single conversation by id
     * @return Inertia\Response 
     */
    public function show($id)
    {
        $conversation = Conversation::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('messages') // TODO: Link messages to conversations
            ->firstOrFail();

        return Inertia::render('ask/ConversationDetail', [
            'conversation' => $conversation,
        ]);
    }
    
    /**
     * PUT conversation/{id}
     * Update the title or the status (is_archived)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'is_archived' => 'nullable|boolean'
        ]);

        $conversation = Conversation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $conversation->update($request->only(['title', 'is_archived']));
        return response()->json($conversation);
    }

    /**
     * DELETE conversation/{id}
     * Delete a conversation
     */
    public function destroy($id)
    {
        $conversation = Conversation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $conversation->delete();

        return response()->json(["message" => 'Conversation deleted']);
    }
}
