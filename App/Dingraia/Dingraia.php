<?php /** @noinspection SpellCheckingInspection */

namespace App\Dingraia;

class Dingraia
{
    public function run(): void
    {
        ob_start();
        $route = new Route();
        $router = require_once APP_PATH . 'App/Route/Route.php';
        $router($route);
        $route->resolve();
    }

    public function s()
    {

    }
}