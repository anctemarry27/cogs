<?php namespace App\Http\Controllers;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

class HomeController extends RoutingController
{
    public function index()
    {
        $name = input('name');
        
        # determine if this call includes a 'name' query
        if ($name)
            // handle /controller/name
            return response("Hello from the HomeController for {$name}!");
        else
            // handle /controller
            return response('Hello from the HomeController!');
    }
}
