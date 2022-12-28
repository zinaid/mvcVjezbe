<?php
namespace App\Controllers;

use Core\Request;

class HomeController
{
    public function home(Request $request)
    {
        $string = "My First Framework";
        $test = $request->test;
        return view('home.index', compact('test'));
    }
}


