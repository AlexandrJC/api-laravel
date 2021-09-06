<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\TransactionController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('testcase/currencyNsi.php/', 'App\Http\Controllers\CurrencyController@index');

Route::get('testcase/list.php/', 'App\Http\Controllers\TransactionController@index');

Route::post('testcase/add.php/','App\Http\Controllers\TransactionController@store');

Route::post('testcase/update.php/', 'App\Http\Controllers\TransactionController@update');

