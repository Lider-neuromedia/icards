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
    return redirect()->route('login');
});

Route::post('analytics/track', 'AnalyticsController@trackAction')->name('analytics.track');

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('analytics/{client}/download', 'AnalyticsController@download')->name('analytics.download');
});

Route::prefix('admin')->middleware(['auth', 'role:admin'])->namespace('Admin')->group(function () {
    Route::resource('sellers', 'SellersController', ['except' => ['show']]);
    Route::resource('clients', 'ClientsController', ['except' => ['show']]);
    Route::resource('clients.cards', 'CardsController', ['except' => ['show']]);
    Route::get('clients/{client}/theme', 'CardsController@theme')->name('clients.cards.theme');
    Route::post('clients/{client}/theme', 'CardsController@storeTheme')->name('clients.cards.theme-store');
    Route::post('cards/{card}/number', 'CardsController@updateCardNumber')->name('cards.number');
    Route::resource('users', 'AdminsController', ['except' => ['show']]);
});

Route::prefix('clients')->middleware(['auth', 'role:client'])->namespace('Clients')->group(function () {
    Route::get('profile', 'ProfileController@index')->name('profile.index');
    Route::post('profile', 'ProfileController@store')->name('profile.store');
    Route::resource('cards', 'CardsController', ['except' => ['show']]);
    Route::get('/theme', 'CardsController@theme')->name('cards.theme');
    Route::post('/theme', 'CardsController@storeTheme')->name('cards.theme-store');
    Route::post('cards/{card}/number', 'CardsController@updateCardNumber')->name('cards.number');
});

Auth::routes(['register' => false]);

Route::get('/home', 'HomeController@index')->name('home');

if (false) {
    Route::get('/setup-storage', function () {
        $exitCode = Artisan::call('storage:link');
        return response()->json('done', 200);
    });

    Route::get('/clear-cache', function () {
        $exitCode = Artisan::call('config:clear');
        $exitCode = Artisan::call('cache:clear');
        $exitCode = Artisan::call('config:cache');
        $exitCode = Artisan::call('view:clear');
        $exitCode = Artisan::call('route:clear');
        return response()->json('done', 200);
    });

    Route::get('/setup-db', function () {
        $exitCode = Artisan::call('migrate --seed --no-interaction');
        return response()->json('done', 200);
    });
}

Route::get('/ec/{card}', 'CardsController@card');
Route::get('/{client}/{card}', 'CardsController@clientCard');
