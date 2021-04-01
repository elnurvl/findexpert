<?php

use App\Http\Controllers\TopicController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    return $user->createToken($request->device_name)->plainTextToken;
});

Route::middleware('auth:sanctum')->resource('users', UserController::class)->only(['index', 'show']);
Route::middleware('auth:sanctum')->get('users/{user}/friends', [UserController::class, 'getFriends']);
Route::middleware('auth:sanctum')->post('users/{user}/add-friend', [UserController::class, 'addFriend']);

Route::middleware('auth:sanctum')->get('users/{user}/topics', [TopicController::class, 'index']);
Route::middleware('auth:sanctum')->get('topics/search', [TopicController::class, 'search']);
