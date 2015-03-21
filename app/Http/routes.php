<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
 */


//Route::get('/', array('as' => 'home', function()
//{
//	Session::put('id', 3);		
//    return View::make('home');
//}));

Route::get('/', 'MainController@getIndex');
//Route::get('/', 'WelcomeController@index');
Route::get('home', 'HomeController@index');

Route::controllers([
        'auth' => 'Auth\AuthController',
        'password' => 'Auth\PasswordController',
]);

Route::get('users', function()
{
    return View::make('users');
});

Route::get('auth/getLogout', array('as' => 'logout', 'uses' => 'AuthController@getLogout'));
Route::controller('main', 'MainController');
Route::get('bond', 'BondController@getIndex');
Route::post('bond/update_bond', array('as' => 'update_bond', 'uses' => 'BondController@postIndex'));
Route::get('bonds/{step}', array('as' => 'showBonds', 'uses' => 'BondsController@showResults'));
Route::get('bonds', 'BondsController@getIndex');
Route::post('bonds', array('as' => 'bonds', 'uses' => 'BondsController@postIndex'));
Route::controller('bondview', 'BondviewController');
Route::get('calculation/{step}', array('as' => 'displayBonds', 'uses' => 'CalculationController@showResults'));
Route::get('calculation', 'CalculationController@getIndex');
Route::post('calculation', array('as' => 'calculation', 'uses' => 'CalculationController@postIndex'));
Route::get('calculations/{step}', array('as' => 'showCalculations', 'uses' => 'CalculationsController@showResults'));
Route::get('calculations', 'CalculationsController@getIndex');
Route::post('calculations', array('as' => 'calculations', 'uses' =>'CalculationsController@postIndex'));
Route::get('encashment', 'EncashmentsController@getIndex');
Route::post('encashment/update_encashment', array('as' => 'update_encashment', 'uses' => 'EncashmentsController@postIndex'));
Route::get('extra_details/{bond}', array('as' => 'extra_details', 'uses' => 'DetailsController@getDetails'));
Route::get('increment', 'IncrementsController@getIndex');
Route::post('increment/update_increment', array('as' => 'update_increment', 'uses' => 'IncrementsController@postIndex'));
Route::controller('newbond', 'NewbondController');
Route::controller('newcalculation', 'NewcalculationController');
Route::controller('newpolicyholder', 'NewpolicyholderController');
Route::get('nonresidence', 'NonresidenceController@getIndex');
Route::post('nonresidence/update_nonresidence', array('as' => 'update_nonresidence', 'uses' => 'NonresidenceController@postIndex'));
Route::get('ownership', 'OwnershipController@getIndex');
Route::post('ownership/update_ownership', array('as' => 'update_ownership', 'uses' => 'OwnershipController@postIndex'));
Route::get('policyholder', 'PolicyholderController@getIndex');
Route::post('policyholder/update_policyholder', array('as' => 'update_policyholder', 'uses' => 'PolicyholderController@postIndex'));
Route::get('policyholders/{step}', array('as' => 'showPolicyholders', 'uses' => 'PolicyholdersController@showResults'));
Route::get('policyholders', 'PolicyholdersController@getIndex');
Route::post('policyholders', array('as' => 'policyholders', 'uses' => 'PolicyholdersController@postIndex'));
Route::controller('policyholderview', 'PolicyholderviewController');
Route::get('policyloan', 'PolicyloansController@getIndex');
Route::post('policyloan/update_policyloan', array('as' => 'update_policyloan', 'uses' => 'PolicyloansController@postIndex'));
Route::get('relationships', 'RelationshipsController@getIndex');
Route::post('relationships/update_relationships', array('as' => 'update_relationships', 'uses' => 'RelationshipsController@postIndex'));
Route::get('report', 'ReportController@getIndex');
Route::get('genPdf', array('as' => 'genPdf', 'uses' => 'ReportController@genPdf'));
Route::get('segments', 'SegmentsController@getIndex');
Route::post('segments/update_segments', array('as' => 'update_segments', 'uses' => 'SegmentsController@postIndex'));
Route::get('withdrawal', 'WithdrawalsController@getIndex');
Route::post('withdrawal/update_withdrawal', array('as' => 'update_withdrawal', 'uses' => 'WithdrawalsController@postIndex'));


