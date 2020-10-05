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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/home', 'HomeController@index')->name('home');

/*
|--------------------------------------------------------------------------
| ログイン処理
|--------------------------------------------------------------------------
 */
// Auth::routes();

//以降設定変更
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('/password/reset', 'Auth\ResetPasswordController@reset');

/*
|-------------------------------------------------------------------------
| 管理者以上で操作
|-------------------------------------------------------------------------
 */
Route::group(['middleware' => ['auth', 'can:admin']], function () {
  //ユーザー登録
  Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
  Route::post('register', 'Auth\RegisterController@register');
});

// Voyager Route
Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
    Route::post('attendance-line','Voyager\AttendanceLineController@detail')->name('voyager.attendance-lines.detail')->middleware('admin.user');
    Route::post('attendance-line/edit','Voyager\AttendanceLineController@editor')->name('voyager.attendance-lines.editor')->middleware('admin.user');
    // Route::post('attendance-line','Voyager\AttendanceLineController@updator')->name('voyager.attendance-lines.updator')->middleware('admin.user');
    Route::get('attendance-line','Voyager\UserLineController@index')->middleware('admin.user');
});
