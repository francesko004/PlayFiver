<?php

use App\Http\Controllers\Api\Profile\WalletController;
use App\Models\Game;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|Sme
*/

Route::get('clear', function () {
    Artisan::command('clear', function () {
        Artisan::call('optimize:clear');
        return back();
    });

    return back();
});
Route::get('/withdrawal/{id}', [WalletController::class, 'withdrawalFromModal'])->name('withdrawal');
Route::get('/cancelwithdrawal/{id}', [WalletController::class, 'cancelWithdrawal'])->name('cancelwithdrawal');
// GAMES PROVIDER
include_once(__DIR__ . '/groups/provider/playFiver.php');


// GATEWAYS
include_once(__DIR__ . '/groups/gateways/suitpay.php');
include_once(__DIR__ . '/groups/gateways/digitopay.php');
include_once(__DIR__ . '/groups/gateways/ezzepay.php');

/// SOCIAL
include_once(__DIR__ . '/groups/auth/social.php');


// APP
include_once(__DIR__ . '/groups/layouts/app.php');
