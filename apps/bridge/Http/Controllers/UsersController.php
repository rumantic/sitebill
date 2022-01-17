<?php
namespace bridge\Http\Controllers;

class UsersController extends BaseController
{
    public function index()
    {
        return $this->view('pages.users');
    }

    public function store()
    {
        return "creating new user";
    }

    public function json()
    {
        // @todo: определить как передавать response
        return response()->json([
            'name' => 'Abigail',
            'state' => 'CA',
        ]);
    }

}
