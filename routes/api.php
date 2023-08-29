<?php

use App\Http\Controllers\GameController;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('token', function() {
    redirect('/sanctum/csrf-cookie')->send();
});

Route::prefix('game')->controller(GameController::class)->group(function() {
    Route::get('list', 'list');
    Route::get('{id}/detail', 'detail');
    Route::get('{id}/prev/{combination}', 'prev');
    Route::post('create', 'create');
    Route::post('{id}/propose', 'propose');
    Route::delete('{id}/delete', 'delete');
});