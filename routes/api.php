<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JuegoController;


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


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('comenzar', [JuegoController::class, 'iniciarJuego']);
Route::post('unirse', [JuegoController::class, 'ingresarJuego']);
Route::get('jugadores', [JuegoController::class, 'listarUsuarios']);
Route::get('palabra', [JuegoController::class, 'generarPalabraAleatoria']);
Route::post('reset', [JuegoController::class, 'reiniciarJuego']);


