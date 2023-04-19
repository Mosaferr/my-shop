<?php

use App\Http\Controllers\HelloWorldController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
Route::get('/hello', function () {
    return ('Hello Worldzik');
});

Route::get('/hello', function () {
    return view('hello');
});
*/

Route::get('users/list', [UserController::class, 'index'])->middleware('auth');
Route::delete('users/{id}', [UserController::class, 'destroy'])->middleware('auth');
Route::get('hello', [HelloWorldcontroller::class, 'show']);

Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
