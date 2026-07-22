<?php
use Illuminate\Routing\Router;

/** @var $router Router */
if (\SiteBill::getConfigValueStatic('apps.news.enable')) {
    $router->match(array('GET'), \SiteBill::getConfigValueStatic('apps.news.alias'), 'news\Http\Controllers\NewsController@index');
    $router->match(array('GET'), \SiteBill::getConfigValueStatic('apps.news.alias').'/{news_alias}', 'news\Http\Controllers\NewsController@show');
    $router->match(array('GET'), '/news{news_id}.html', 'news\Http\Controllers\NewsController@show');
}
