<?php

use App\Http\Controllers\Api\JobPostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public API routes
Route::prefix('v1')->group(function () {
    // Health check
    Route::get('health', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'API is running',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
        ]);
    });

    // Public job posts (for external integrations)
    Route::get('job-posts/public', [JobPostController::class, 'index'])
        ->middleware('throttle:60,1'); // Rate limit for public access
});

// Protected API routes
Route::prefix('v1')->middleware(['api.auth', 'throttle:api'])->group(function () {
    // User profile
    Route::get('/user', function (Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()->load(['hrDetail', 'jobSeekerDetail']),
            'message' => 'User profile retrieved successfully',
        ]);
    });

    // Job Posts API
    Route::apiResource('job-posts', JobPostController::class);
    Route::post('job-posts/{jobPost}/status', [JobPostController::class, 'changeStatus']);
    Route::get('job-posts/{jobPost}/applications', [JobPostController::class, 'applications']);

    // Token management
    Route::post('auth/tokens', function (Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'array',
        ]);

        $token = $request->user()->createToken(
            $request->name,
            $request->abilities ?? ['*']
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token->plainTextToken,
                'name' => $request->name,
                'abilities' => $token->accessToken->abilities,
            ],
            'message' => 'API token created successfully',
        ]);
    });

    Route::delete('auth/tokens/{tokenId}', function (Request $request, $tokenId) {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Token revoked successfully',
        ]);
    });

    Route::get('auth/tokens', function (Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()->tokens,
            'message' => 'Tokens retrieved successfully',
        ]);
    });
});
