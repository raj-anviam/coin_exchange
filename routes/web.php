<?php

use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\SessionController;
use  App\Http\Controllers\BaseController;

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
    Session::reflash();
    return view('welcome');
});


Route::group(['middleware' => 'btc_rpc'], function () {

    Route::get('/add-session', [SessionController::class, 'create'])->name('session.create');
    Route::post('/intermediate-addess', [SessionController::class, 'intermediateAddessStore'])->name('intermediate-addess.store');
    Route::post('/process-batch', [SessionController::class, 'addJob'])->name('session.process-batch');
    Route::get('/search/{sessionId}', [SessionController::class, 'search'])->name('session.search');
});