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

//首页
Route::get('/','PagesController@root')->name('root');
//登陆注册的相关路由
Auth::routes();

//路由组 只有登陆后的用户才能访问
Route::group(['middleware' => 'auth'],function (){

    //邮箱验证提醒页面
    Route::get('/email_verify_notice','PagesController@emailVerifyNotice')->name('email_verify_notice');

    //路由组 只有通过邮箱验证后的用户才能访问
    Route::group(['middleware' => 'email_verified'], function() {

        
    });


});