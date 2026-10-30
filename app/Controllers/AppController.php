<?php

namespace App\Controllers;

use Core\Controllers\Controller;

class AppController extends Controller
{
    public function index()
    {
        $this->render("home");
    }
}
