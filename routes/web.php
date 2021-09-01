<?php

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

Route::prefix('admin')->middleware(['auth', 'role:admin'])->namespace('Admin')->group(function () {
    Route::resource('clients', 'ClientsController', ['except' => ['show']]);
    Route::resource('users', 'AdminsController', ['except' => ['show']]);
});

Route::prefix('clients')->middleware(['auth', 'role:client'])->namespace('Clients')->group(function () {
    Route::resource('cards', 'CardsController', ['except' => ['show']]);
});

Auth::routes(['register' => false]);

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/ec/{card}', 'CardsController@card');

Route::get('/setup-storage', function () {
    $exitCode = Artisan::call('storage:link');
    return response()->json('done', 200);
});
