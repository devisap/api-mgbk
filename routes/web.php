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

$router->get('/', 'UserController@store');

// user
// -- GET METHOD --
$router->get('/user/profile/{id_user}', 'UserController@getProfile');
// -- POST METHOD
$router->post('/user/register', 'UserController@register');
$router->post('/user/login', 'UserController@login');
$router->post('/user/profile', 'UserController@setProfile');

