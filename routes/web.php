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

$router->get('/', 'ICarServiceController@index');


$router->group(

    ['middleware' => 'auth'],
    function ($router) {

        $router->get('test-auth', 'ICarServiceController@testAuth');
        $router->get('spisak-komisija', 'ICarServiceController@spisakKomisija');
        $router->post('get-ident', 'ICarServiceController@getIdent');
    }

);
