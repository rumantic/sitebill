<?php
use Illuminate\Routing\Router;

/** @var $router Router */
//if (\SiteBill::getConfigValue('apps.admin3.enable')) {
    $router->match(array('GET', 'POST'), \SiteBill::getConfigValue('apps.admin3.alias'), 'admin3\Http\Controllers\Admin3Controller@index');
    $router->match(array('GET', 'POST'), \SiteBill::getConfigValue('apps.admin3.alias').'/profile', 'admin3\Http\Controllers\Admin3Controller@profile');
    /*
    $router->match(array('GET'), \SiteBill::getConfigValue('apps.complex.alias').'/special', 'complex\Http\Controllers\ComplexController@special');
    $router->match(array('GET'), \SiteBill::getConfigValue('apps.complex.alias').'/{slug}', 'complex\Http\Controllers\ComplexController@common');
    */
//}
