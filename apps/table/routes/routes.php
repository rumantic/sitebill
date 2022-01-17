<?php
use Illuminate\Routing\Router;

/** @var $router Router */
if (\SiteBill::getConfigValue('apps.admin3.enable')) {
    $router->match(array('GET', 'POST'), \SiteBill::getConfigValue('apps.admin3.alias').'/tables', 'table\Http\Controllers\TableController@tablelist');
    $router->match(array('GET', 'POST'), \SiteBill::getConfigValue('apps.admin3.alias').'/tables/{id}', 'table\Http\Controllers\TableController@tableview')->where(['id' => '[0-9]+']);

    $router->match(array('GET', 'POST'), \SiteBill::getConfigValue('apps.admin3.alias').'/tables/new', 'table\Http\Controllers\TableController@tablenew');
    $router->match(array('GET', 'POST'), \SiteBill::getConfigValue('apps.admin3.alias').'/tables/{id}/delete', 'table\Http\Controllers\TableController@tabledelete')->where(['id' => '[0-9]+']);
    $router->match(array('GET', 'POST'), \SiteBill::getConfigValue('apps.admin3.alias').'/tables/{id}/edit', 'table\Http\Controllers\TableController@tableedit')->where(['id' => '[0-9]+']);
    $router->match(array('GET', 'POST'), \SiteBill::getConfigValue('apps.admin3.alias').'/tables/{id}/update', 'table\Http\Controllers\TableController@tableupdate')->where(['id' => '[0-9]+']);
    $router->match(array('GET', 'POST'), \SiteBill::getConfigValue('apps.admin3.alias').'/tables/{id}/create', 'table\Http\Controllers\TableController@tablecreate')->where(['id' => '[0-9]+']);


}$router->match(array('GET', 'POST'), \SiteBill::getConfigValue('apps.admin3.alias').'/tables/{id}/columncreate', 'table\Http\Controllers\TableController@columncreate')->where(['id' => '[0-9]+']);

$router->match(array('GET', 'POST'), \SiteBill::getConfigValue('apps.admin3.alias').'/tables/{id}/handlercreate', 'table\Http\Controllers\TableController@handlercreate')->where(['id' => '[0-9]+']);
$router->match(array('GET', 'POST'), \SiteBill::getConfigValue('apps.admin3.alias').'/tables/{id}/handleredit', 'table\Http\Controllers\TableController@handleredit')->where(['id' => '[0-9]+']);
