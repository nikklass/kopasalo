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

Route::get('/check/employees/name/{mobile}','GeneralController@getName');
Route::get('/check/employees/maximum-amount/{mobile}/{payrol}','GeneralController@getMaxDebt');
Route::get('/check/employee/total-loan/{mobile}/{payrol}','GeneralController@checkTotalLoan');
Route::get('/check/employee/loan-history/{mobile}/{payrol}','GeneralController@checkLoanHistory');
Route::post('/receive/withdrawal/transaction','GeneralController@processTransaction');
Route::post('/receive/loan-repayment/transaction','GeneralController@processRepayment');
Route::get('/whitelist/employees/{mobile}/{firstname}','GeneralController@whitelist');

// Route::get('/check/employees/name','GeneralController@getName');
// Route::get('/check/employees/maximum-amount','GeneralController@getMaxDebt');
// Route::get('/receive/withdrawal/transaction','GeneralController@processTransaction');
