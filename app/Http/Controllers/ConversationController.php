<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConversationController extends Controller
{
    /**
     * GET /conversation
     * Base Dashboard view
     * @return Inertia\Response 
     */
    public function index()
    {
        return Inertia::render('conversations/Index', [ 
                    'conversations' => $this->getConversations(),
                    'selectedId'    => null,
                    'messages'      => [],
                ]);
    }

    /**
     * GET conversation/{id}
     * Get a single conversation by id
     */
    public function show($id)
    {
        return Inertia::render('conversations/Index', [
            'conversations' => $this->getConversations(), // Still need the sidebar
            'selectedId'    => (int) $id,
            'messages'      => Message::where('conversation_id', $id)->get(),
        ]);
    }

    /**
     * Helper function, just gets the conversations nothing more
     */
    public function getConversations()
    {
        return Conversation::where('user_id', Auth::id())
            ->where('is_archived', false)
            ->orderBy('updated_at', 'desc')
            ->get();
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

        return redirect('/conversations');
    }
}
