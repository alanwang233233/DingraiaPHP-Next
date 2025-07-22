<?php

namespace App\Dingraia;
class Dingraia
{
    public function run(): void
    {
        ob_start();
        $router = new Route();
        $router->resolve();
    }
}