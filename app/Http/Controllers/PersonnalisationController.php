<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Instruction;
use Illuminate\Support\Facades\Auth;

class PersonnalisationController extends Controller
{
    public function index()
    {
        $instruction = Instruction::where('user_id', Auth::id())->first();

        return Inertia::render('personnalisation/Instructions', [
            'assistant_instructions' => $instruction?->assistant_instructions,
            'user_instructions' => $instruction?->user_instructions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_instructions'      => 'nullable|string|max:2000',
            'assistant_instructions' => 'nullable|string|max:2000',
        ]);

        Instruction::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return redirect()->back()->with('success', 'Instructions saved.');
    }
}