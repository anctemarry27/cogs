<?php namespace App\Http\Controllers;

use Og\Events;
use Og\Interfaces\ContainerInterface;
use Og\Support\Cogs\Collections\Input;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
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
