<?php
use Illuminate\Routing\Router;

/** @var $router Router */
//$router->group(['prefix' => '{locale}', 'where' => ['locale' => '[a-zA-Z]{2}']], function($router){
    $router->match(array('GET', 'POST'), 'ipotekaorder', 'bridge\Http\Controllers\BlackBoxController@ipotekaorder');
    $router->match(array('GET', 'POST'), 'contactus', 'bridge\Http\Controllers\BlackBoxController@contactus');
    $router->match(array('GET', 'POST'), 'add', 'bridge\Http\Controllers\BlackBoxController@add');
    $router->match(array('GET', 'POST'), 'login', 'bridge\Http\Controllers\BlackBoxController@login');
    $router->match(array('GET'), 'logout', 'bridge\Http\Controllers\BlackBoxController@logout');
    $router->match(array('GET', 'POST'), 'register', 'bridge\Http\Controllers\BlackBoxController@register');
    $router->match(array('GET', 'POST'), 'remind', 'bridge\Http\Controllers\BlackBoxController@remind');
    $router->match(array('GET'), 'user{id}.html', 'bridge\Http\Controllers\BlackBoxController@userlisting')->where(['id' => '[0-9]+']);
    $router->match(array('GET', 'POST'), 'robox', 'bridge\Http\Controllers\BlackBoxController@robox');
    $router->match(array('GET', 'POST'), 'robox/result', 'bridge\Http\Controllers\BlackBoxController@robox');
    $router->match(array('GET', 'POST'), 'robox/success', 'bridge\Http\Controllers\BlackBoxController@robox');
    $router->match(array('GET'), 'myfavorites', 'bridge\Http\Controllers\BlackBoxController@myfavorites');
    $router->match(array('GET', 'POST'), 'account/data', 'bridge\Http\Controllers\BlackBoxController@account_data');
    $router->match(array('GET', 'POST'), 'account/profile', 'bridge\Http\Controllers\BlackBoxController@account_profile');

    $router->match(array('GET'), 'map', 'bridge\Http\Controllers\BlackBoxController@map');
    $router->match(array('GET'), 'map_full_screen', 'bridge\Http\Controllers\BlackBoxController@map_full_screen');

    $router->match(array('GET'), 'team', 'bridge\Http\Controllers\BlackBoxController@team');

    $router->match(array('GET'), 'about', 'bridge\Http\Controllers\BlackBoxController@about');
    $router->match(array('GET'), 'partners', 'bridge\Http\Controllers\BlackBoxController@partners');
    $router->match(array('GET'), 'vacancy', 'bridge\Http\Controllers\BlackBoxController@vacancy');
    $router->match(array('GET'), 'compare', 'bridge\Http\Controllers\BlackBoxController@compare');
    $router->match(
        array('GET', 'POST'),
        'client/order/{entity}',
        'bridge\Http\Controllers\BlackBoxController@client_order_entity'
    )->where('entity', '(.*)');

// catch-all route
    $router->any('{any}', 'bridge\Http\Controllers\BlackBoxController@index')->where('any', '(.*)');
//});

