<?php

namespace App\Http\Modules\Feedback\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Modules\Feedback\Model\Feedback;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'message' => 'required|string'
        ]);

        $feedback = Feedback::create([
            'entity_id' => auth()->user()->entity_id,
            'user_id' => auth()->id(),
            'type' => $request->type,
            'rating' => $request->rating,
            'message' => $request->message
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback registrado con éxito',
            'data' => $feedback
        ], 201);
    }
}
