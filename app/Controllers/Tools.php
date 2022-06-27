<?php

namespace App\Controllers;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Controller;

class Tools extends Controller
{

    public function message($to = 'World')
    {
        CLI::write("Hello ${to}!");
    }
}