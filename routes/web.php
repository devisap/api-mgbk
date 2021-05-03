<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    // return $router->app->version();
    return $router->app->version();
});

// GET GENERATE APP KEY
$router->get('/key', function () {
    return \Illuminate\Support\Str::random(32);
});

// user
// -- GET METHOD --
$router->get('/user/profile/{id_user}', 'UserController@getProfile');
// -- POST METHOD
$router->post('/user/register', 'UserController@register');
$router->post('/user/login', 'UserController@login');
$router->post('/user/profile', 'UserController@setProfile');

// school
// -- GET METHOD --
$router->get('/school', 'SchoolController@getList');
// -- POST METHOD --
$router->post('/school', 'SchoolController@store');

// activity
// -- GET METHOD --
$router->get('/activity', 'ActivityController@getList');
// -- POST METHOD --
$router->post('/activity', 'ActivityController@store');


$router->get('report/load-weeks', 'ReportController@loadWeeks');

// -- Report -- //
$router->get('/report/by-date/{tanggal}', 'ReportController@getReportByDate');
$router->get('/report/by-week', 'ReportController@getReportByWeek');
$router->get('/report/by-month', 'ReportController@getReportByMonth');
$router->get('/report/by-semester', 'ReportController@getReportBySemester');
$router->get('/report/by-year', 'ReportController@getReportByYear');
$router->post('/report/destroy', 'ReportController@destroyReport');

// -- Print Rport -- //
$router->get('/print-report/by-date/{tanggal}', 'ReportController@printReportByDate');
$router->get('/print-report/by-week', 'ReportController@printReportByWeek');
$router->get('/print-report/by-month', 'ReportController@printReportByMonth');
$router->get('/print-report/by-semester', 'ReportController@printReportBySemester');
$router->get('/print-report/by-year', 'ReportController@printReportByYear');
// -- POST METHOD --
$router->post('/report', 'ReportController@store');
