<?php
namespace bridge\Http;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Pipeline;
use Illuminate\Routing\Router;


class Kernel
{
    /**
     * @param false $silent - если указать режим silent, тогда никакого вывода не сделает (для старых версий вывода Sitebill)
     */
    function handle($silent = false) {
        // Create new IoC Container instance
        $container = new Container;

        // Using Illuminate/Events/Dispatcher here (not required); any implementation of
        // Illuminate/Contracts/Event/Dispatcher is acceptable
        $events = new Dispatcher($container);
        \SiteBill::register_illuminate_event_dispatcher($events);
        if ( $silent ) {
            // В режиме $silent выполняем только инициализацию диспетчера
            return false;
        }


        // Create the router instance
        $router = new Router($events);

        // Global middlewares
        $globalMiddleware = [
            // \App\Middleware\StartSession::class,
        ];

        // Array middlewares
        $routeMiddleware = [
            // 'auth' => \App\Middleware\Authenticate::class,
            // 'guest' => \App\Middleware\RedirectIfAuthenticated::class,
        ];

        // Load middlewares to router
        foreach ($routeMiddleware as $key => $middleware) {
            $router->aliasMiddleware($key, $middleware);
        }

        // Load the routes
        if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.\SiteBill::getConfigValue('theme').'/routes/routes.php') ) {
            require_once SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.\SiteBill::getConfigValue('theme').'/routes/routes.php';
        }
        $routes_apps = array(
            'agents',
            'articles',
            'complex',
            'admin3',
            'reviewer',
            'table'
        );

        foreach ( $routes_apps as $app_name ) {
            if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/'.$app_name.'/routes/routes.php') ) {
                require_once SITEBILL_DOCUMENT_ROOT.'/apps/'.$app_name.'/routes/routes.php';
            }
        }



        require_once SITEBILL_DOCUMENT_ROOT.'/apps/bridge/routes/routes.php';

        // Create a request from server variables
        $request = Request::capture();

        // Dispatching the request:
        // When it comes to dispatching the request, you have two options:
        // a) you either send the request directly through the router
        // or b) you pass the request object through a stack of (global) middlewares
        // then dispatch it.

        // a. Dispatch the request through the router
        // $response = $router->dispatch($request);

        // b. Pass the request through the global middlewares pipeline then dispatch it through the router
        $response = (new Pipeline($container))
            ->send($request)
            ->through($globalMiddleware)
            ->then(function ($request) use ($router) {
                return $router->dispatch($request);
            });

        // Send the response back to the browser
        if ( !$silent ) {
            $response->send();
        }
    }
}
