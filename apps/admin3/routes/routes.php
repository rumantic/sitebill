<?php
use Illuminate\Routing\Router;

/** @var $router Router */
//if (\SiteBill::getConfigValueStatic('apps.admin3.enable')) {
    $router->match(array('GET', 'POST'), \SiteBill::getConfigValueStatic('apps.admin3.alias'), 'admin3\Http\Controllers\Admin3Controller@index');
    $router->match(array('GET', 'POST'), \SiteBill::getConfigValueStatic('apps.admin3.alias').'/profile', 'admin3\Http\Controllers\Admin3Controller@profile');
    /*
    $router->match(array('GET'), \SiteBill::getConfigValueStatic('apps.complex.alias').'/special', 'complex\Http\Controllers\ComplexController@special');
    $router->match(array('GET'), \SiteBill::getConfigValueStatic('apps.complex.alias').'/{slug}', 'complex\Http\Controllers\ComplexController@common');
    */
//}
