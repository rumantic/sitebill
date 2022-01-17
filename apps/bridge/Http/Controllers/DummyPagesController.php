<?php
namespace bridge\Http\Controllers;

class DummyPagesController extends BaseController
{
    public function index($page_uri, $view = 'pages.services.default')
    {
        $params['page_uri'] = $page_uri;
        return $this->return_pageview($view, $params);
    }
}
