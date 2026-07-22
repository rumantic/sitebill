<?php
namespace bridge\Http\Controllers;

class MapController extends BaseController
{
    public function index()
    {
        $params = [];
        $this->sitebill::set_template_store('title', 'Объекты на карте');
        return $this->return_pageview('map-page', $params);
    }
}
