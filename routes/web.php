<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController; 
use App\Http\Controllers\TasksController;
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


Route::get('/dashboard', [TasksController::class, 'index'])->middleware(['auth'])->name('dashboard');
require __DIR__.'/auth.php';


Route::group(['middleware' => ['auth']], function () {          
  Route::get('/', [TasksController::class, 'index']);
    Route::resource('tasks', TasksController::class, ['only' => ['create','show','edit','update','store', 'destroy']]);
});// 追記
