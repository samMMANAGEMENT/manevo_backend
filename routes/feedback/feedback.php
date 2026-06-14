<?php

use App\Http\Modules\Feedback\Controller\FeedbackController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('feedbacks', [FeedbackController::class, 'store']);
});
