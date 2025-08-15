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
    $router->any(['/chat/dingtalk/<uid>','/chat/<uid>'], [\App\Controller\DingtalkChat::class, 'main'])
        ->middleware(new class
        {
            public function handle($next): mixed
            {
                echo "中间件";
                return $next();
            }
        });
};

