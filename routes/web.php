<?php

use App\Http\Controllers\Controller;
use App\Models\User;
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
    return $router->app->version();
});


$router->get('user', 'AuthController@getUser');
$router->post('register', 'AuthController@register');
$router->post('login', 'AuthController@login');
$router->post('logout', 'AuthController@logout');

$router->group(['prefix' => 'app', 'middleware' => 'auth'], function() use($router){
    $router->group(['middleware' => 'user'], function() use($router) {
        $router->get('/profile/detail', 'UserController@show_profile');
        $router->put('/profile/update-password', 'UserController@update_password');
        $router->get('/', 'BookController@book_show');
        $router->post('/', 'BookController@book_store');
        $router->get('/logactivity', 'LogActivityController@log_show');
        $router->get('/komship', 'LogActivityController@komship');
    });
});