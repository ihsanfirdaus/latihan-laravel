<?php

use App\Events\MessageCreated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/message/created', function() {
    MessageCreated::dispatch('welcome to the application');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/realtime-chat', [App\Http\Controllers\HomeController::class, 'realtimeChat'])->name('home');
Route::get('/realtime-chat-v2', [App\Http\Controllers\HomeController::class, 'realtimeChatV2'])->name('home');

Route::get('/login/sso', [App\Http\Controllers\Auth\LoginController::class, 'sso'])->name('login.sso');