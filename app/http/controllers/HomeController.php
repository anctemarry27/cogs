<?php namespace App\Http\Controllers;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Support\Cogs\Collections\Input;
use Psr\Http\Message\ResponseInterface as Response;

class HomeController extends Controller
{
    /**
     * @param Input    $params
     * @param Response $response
     *
     * @return int
     */
    public function index(Input $params, Response $response)
    {
        # determine if this call includes a 'name' query
        if ($params->has('name'))
            return $response->getBody()->write("Hello from the HomeController for {$params['name']}!");
        else
            return $response->getBody()->write('Hello from the HomeController!');
    }

}
