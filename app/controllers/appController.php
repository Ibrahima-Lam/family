<?php

namespace App\Controllers;

use Core\Controllers\Controller;

class appController extends Controller
{
    public function index()
    {
        $this->render("home");
    }
}
