<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'StartController@start')->name('start');

Route::get('import/getDetails', 'ImportController@getDetails')->name('getDetails');
Route::get('import/start', 'ImportController@startImport')->name('startImport');
Route::get('import/{accountHash}', 'ImportController@startImportFromPreset')->name('importHash');

Route::get('fints/login', 'FinTsController@login')->name('startLogin');
Route::get('fints/accounts', 'FinTsController@fetchAccounts')->name('fetchAccounts');
Route::get('fints/transactions', 'FinTsController@fetchTransactions')->name('fetchTransactions');
Route::get('fints/tan', 'TanController@handleTanRequest')->name('enterTan');
Route::get('fints/tanDevice', 'TanController@chooseTanMedium')->name('chooseTanMedium');

Route::post('fints/tan', 'TanController@submitTanRequest')->name('submitTanRequest');
Route::post('fints/tanDevice', 'TanController@selectTanMedium')->name('selectTanMedium');
Route::post('import/getDetails', 'ImportController@saveDetails')->name('saveDetails');
