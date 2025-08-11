<?php
namespace App\Dingraia;
/**
 * 消息路由
 * 用于处理机器人消息
 */
class MessageRoute
{
    /**
     * 路由列表
     * 格式:
     * [
     *     '/command <param>' => [
     *         'action' => ['controllerName','actionName'],
     *         'middleware' => ['middlewareName']
     *     ]
     * ]
     * @var array
     */
    private array $routes = [];
    /**
     * 当前路由参数
     * @var string[]
     */
    private array $currentParams = [];
    private array $currentRoute;
    private array $globalMiddleware = [];
    public function middleware(array|string|object $middleware): static
    {
        $this->globalMiddleware = $middleware;
        return $this;
    }

    /**
     * 新路由
     * @param string|array $routes
     * @param array|string|callable $action 方法
     * @param array|string|object $middleware 中间件
     * @return MessageRoute
     */
    public function map(string|array $routes, array|string|callable $action, array|string|object $middleware): static
    {
        $routes = is_string($routes) ? [trim($routes)] : array_map('trim',$routes);
        foreach ($routes as $r) {
            $this->routes[$r] = [
                'action' => $action,
                'middleware' => $middleware
            ];
        }
        return $this;
    }

    public function resolve(string $message): mixed
    {
        if ($this->checkMatch($message)) {
            return $this->pipeline();
        }
        return null;
    }

    private function pipeline() : mixed
    {
        $next = function () {
            return $this->runAction();
        };
        $middlewares = array_merge($this->globalMiddleware, $this->currentRoute['middleware']);
        $middlewares = array_reverse($middlewares);
        foreach ($middlewares as $middleware) {
            $next = function () use ($middleware, $next) {
                $instance = new $middleware();
                return $instance->handle($next);
            };
        }
        return $next();
    }
    /**
     * 检查路由是否匹配并提取路径参数
     * @param string $message 消息
     * @return array|bool 匹配数据或false
     */
    private function checkMatch(string $message): array|bool
    {
        foreach ($this->routes as $route => $data) {
            $this->currentRoute['route'] = $route;
            $this->currentRoute['action'] = $data['action'];
            $this->currentRoute['middleware'] = $data['middleware'];
            # 静态路由
            if ($route === $message) {
                return true;
            }
            # 动态路由
            if (str_contains($route, '<')) {
                $routeRegex = $this->regexConvert($route);
                if (preg_match($routeRegex, $message, $matches)) {
                    $this->currentParams = $matches;
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * 执行路由
     * @return mixed
     */
    private function runAction(): mixed
    {
        $action = $this->currentRoute['action'];
        if (is_callable($action)) {
            return call_user_func($action, $this->currentParams);
        }
        if (is_array($action)) {
            $controllerName = $action[0];
            $methodName = $action[1];
            $controller = new $controllerName();
            return $controller->$methodName($this->currentParams);
        }
        if (is_string($action)) {
            $action = explode('@', $action);
            $controllerName = $action[0];
            $methodName = $action[1];
            $controller = new $controllerName();
            return $controller->$methodName(...$this->currentParams);
        }
        return null;
    }

    /**
     * 将路由规则转换为正则表达式
     * 例如: /command <param> 转换为 /command (?<param>[^/]+)
     * @param string $route 路由规则
     * @return string 正则表达式
     */
    private function regexConvert(string $route): string
    {
        $route = preg_replace('/<([^>]+)>/', '(?<$1>[^/]+)', $route);
        return '#^' . $route . '$#';
    }
}