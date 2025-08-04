<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace App\Route;

use App\Dingraia\Route;

/**
 * @param Route $router
 * @return void
 */
return function (Route $router) {
    $router->map('GET', '/', function () {
        echo "首页";
    });
    $router->any('/chat/dingtalk', [\App\Controller\DingtalkChat::class, 'main'])
        ->middleware([\App\Middleware\RequestLog::class]);
};

