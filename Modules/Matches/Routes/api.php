<?php

use Illuminate\Support\Facades\Route;
use Modules\Matches\Http\Controllers\SearchController;
use Modules\Matches\Http\Controllers\DashboardController;
use Modules\Matches\Http\Middleware\Authorize;

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

Route::middleware(['auth:sanctum', Authorize::class])->group(function () {

    Route::controller(SearchController::class)->group(function () {
        Route::get('matches/search', 'search');
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('matches/dashboard', 'dashboard');
    });

    Route::resource('matches/matches', 'MatchesController');
    Route::resource('matches/forms', 'MatchesFormsController');
    Route::resource('matches/profiles', 'MatchesProfilesController');
});

