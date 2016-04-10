<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('like-me', [
    'as' => 'like-me', 'uses' => 'likeme\LikeMeController@view'
    ]);

Route::get('like/{count}', [
    'as' => 'like', 'uses' => 'likeme\LikeMeController@like'
    ]);

Route::group(['prefix' => 'trello'], function () {
    Route::post('overview', [
        'as' => 'overview', 'uses' => 'trello\TaskController@overview'
        ]);
    Route::get('overview', [
        'as' => 'overview-get', 'uses' => 'trello\TaskController@overviewGet'
        ]);
    Route::post('save-stage', [
        'as' => 'save-stage', 'uses' => 'trello\TaskController@saveStage'
        ]);


    Route::get('history-overview', [
        'as' => 'history-overview', 'uses' => 'trello\TaskController@historyOverview'
        ]);
    Route::post('history/{planDate}', [
        'as' => 'history', 'uses' => 'trello\TaskController@history'
        ]);
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    //
});
