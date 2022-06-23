<?php

namespace App\Controllers;

class Bob extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function test()
    {
        return view('welcome_message');
    }

}
