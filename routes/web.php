<?php

    // should do a check here to match $_SERVER['HTTP_ORIGIN'] to a
    // whitelist of safe domains
    header("Access-Control-Allow-Origin: '*'");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;

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

// Route::get('/{any}', [ApplicationController::class, 'index'])->where('any', '^(?!api).*$');
Route::get('/', function () {
    return view('application');
});
// Route::get('/admin/registration-confirmation/{hash}', 'Auth\LoginController@confirmRegistration')->name('confirm_admin_reg');
