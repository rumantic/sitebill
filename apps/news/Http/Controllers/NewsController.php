<?php
namespace news\Http\Controllers;


use bridge\Http\Controllers\BaseController;

class NewsController extends BaseController
{
    /**
     * @var \news_site
     */
    private $object_manager;

    function __construct()
    {
        parent::__construct();
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/news/site/site.php');
        $this->object_manager = new \news_site();
    }

    function index()
    {
        $this->object_manager->frontend();
        return $this->return_pageview('apps.news.resources.views.index', []);
    }

    function show()
    {
        $this->object_manager->frontend();
        return $this->return_pageview('apps.news.resources.views.show', []);
    }

}
