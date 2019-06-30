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
     //\Log::info('Just creating the log file secof a visit');
     return view('welcome');
 });

Route::get('/concerts/{id}', 'ConcertsController@show');

Route::post('/concerts/{id}/orders', 'ConcertOrdersController@store');

Route::get('/orders/{confirmation_number}', 'OrdersController@show');