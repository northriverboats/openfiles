<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('app');
    }

    public function getTest()
    {
        return view('welcome_message');
    }

}
