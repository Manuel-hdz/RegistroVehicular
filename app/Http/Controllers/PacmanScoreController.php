<?php

namespace App\Http\Controllers;

use App\Models\PacmanScore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PacmanScoreController extends Controller
{
    public function index(): JsonResponse
    {
        $scores = PacmanScore::with('user:id,name')
            ->orderByDesc('score')
            ->orderBy('created_at')
            ->limit(10)
            ->get()
            ->map(fn (PacmanScore $score): array => [
                'name' => $score->user?->name ?? 'Usuario',
                'score' => $score->score,
                'difficulty' => $score->difficulty,
            ]);

        return response()->json(['scores' => $scores]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:100000'],
            'difficulty' => ['required', 'string', 'in:easy,normal,hard'],
        ]);

        PacmanScore::create([
            'user_id' => $request->user()->id,
            'score' => $data['score'],
            'difficulty' => $data['difficulty'],
        ]);

        return $this->index();
    }
}