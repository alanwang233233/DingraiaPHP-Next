<?php
namespace App\Dingraia;
class MessageRoute
{
    /**
     * 路由列表
     * 格式:
     * [
     *     '/command <param>' => [
     *         'action' => ['controllerName','actionName'],
     *         'middleware' => ['middlewareName']
     *     ],
     *     '^/user \d+$' => [
     *         'action' => callable,
     *         'middleware' => object
     *     ],
     *     '/command3' => [
     *         'action' => 'controllerName@actionName',
     *         'middleware' => object
     *     ],
     * ]
     * @var array
     */
    private array $routes = [];

    /**
     * 新路由
     * @param string|array $routes
     * @param array|string|callable $action 方法
     * @param array|string|object $middleware 中间件
     * @return MessageRoute
     */
    public function map(string|array $routes, array|string|callable $action, array|string|object $middleware): static
    {
        $routes = is_string($routes) ? [$routes] : $routes;
        foreach ($routes as $r) {
            $this->routes[$r] = [
                'action' => $action,
                'middleware' => $middleware
            ];
        }
        return $this;
    }
}