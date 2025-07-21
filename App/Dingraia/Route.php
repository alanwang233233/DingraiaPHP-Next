<?php

namespace App\Dingraia;
/**
 * 路由管理器，处理HTTP请求并将其分发到对应的控制器方法
 */
class Route
{
    /**
     * 存储注册的路由信息
     * 格式: [路径 => [请求方法 => 处理动作]]
     * @var array
     */
    public array $routes = [];

    /**
     * 注册GET请求的路由
     *
     * @param string|array $path 路由路径
     * @param callable|array $action 路由匹配时执行的动作
     * @return $this 返回当前Route实例，支持链式调用
     */
    public function get(string|array $path, callable|array $action): static
    {
        return $this->map(['GET'], $path, $action);
    }

    /**
     * 注册支持多种HTTP请求方法的路由
     *
     * @param string|array $methods 支持的HTTP请求方法数组，如['GET', 'POST']
     * @param string|array $paths 路由路径
     * @param callable|array $action 路由匹配时执行的动作，可以是回调函数或控制器方法数组
     * @return $this 返回当前Route实例，支持链式调用
     */
    public function map(string|array $methods, string|array $paths, callable|array $action): static
    {
        $methods = is_string($methods) ? [$methods] : $methods;
        $paths = is_string($paths) ? [$paths] : $paths;

        foreach ($paths as $path) {
            foreach ($methods as $method) {
                $this->routes[$path][$method] = $action;
            }
        }

        return $this;
    }

    /**
     * 注册POST请求的路由
     *
     * @param string|array $path 路由路径
     * @param callable|array $action 路由匹配时执行的动作
     * @return $this 返回当前Route实例，支持链式调用
     */
    public function post(string|array $path, callable|array $action): static
    {
        return $this->map(['POST'], $path, $action);
    }

    /**
     * 注册PUT请求的路由
     *
     * @param string $path 路由路径
     * @param callable|array $action 路由匹配时执行的动作
     * @return $this 返回当前Route实例，支持链式调用
     */
    public function put(string $path, callable|array $action): static
    {
        return $this->map(['PUT'], $path, $action);
    }

    /**
     * 注册DELETE请求的路由
     *
     * @param string $path 路由路径
     * @param callable|array $action 路由匹配时执行的动作
     * @return $this 返回当前Route实例，支持链式调用
     */
    public function delete(string $path, callable|array $action): static
    {
        return $this->map(['DELETE'], $path, $action);
    }

    /**
     * 注册支持所有HTTP请求方法的路由
     *
     * @param string $path 路由路径
     * @param callable|array $action 路由匹配时执行的动作
     * @return $this 返回当前Route实例，支持链式调用
     */
    public function any(string $path, callable|array $action): static
    {
        return $this->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'], $path, $action);
    }

    /**
     * 解析当前HTTP请求并执行匹配的路由动作
     *
     * @return mixed 路由处理结果
     */
    public function resolve(): mixed
    {
        $requestMethod = $this->getRequestMethod();
        $requestUri = $_SERVER['REQUEST_URI'];
        $path = strtok($requestUri, '?');

        foreach ($this->routes as $routePath => $methods) {
            if ($this->matchRoute($routePath, $path, $params)) {
                if (isset($methods[$requestMethod])) {
                    return $this->executeAction($methods[$requestMethod], $params);
                }
            }
        }

        return $this->executeAction(['ErrorController', 'notFound'], []);
    }

    /**
     * 获取实际的请求方法（支持_method参数覆盖）
     *
     * @return string HTTP请求方法，如GET、POST等
     */
    private function getRequestMethod(): string
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        return $method;
    }

    /**
     * 检查路由是否匹配并提取路径参数
     *
     * @param string $route 注册的路由路径
     * @param string $path 当前请求的路径
     * @param array &$params 引用传递，用于存储提取的路径参数
     * @return bool 如果匹配成功返回true，否则返回false
     */
    private function matchRoute(string $route, string $path, array &$params): bool
    {
        $params = [];
        // 处理静态路由
        if ($route === $path) {
            return true;
        }
        // 处理动态路由（带参数）
        if (str_contains($route, '<')) {
            $routeRegex = $this->convertRouteToRegex($route);
            if (preg_match($routeRegex, $path, $matches)) {
                $params = array_slice($matches, 1);
                return true;
            }
        }

        return false;
    }

    /**
     * 将路由规则转换为正则表达式模式
     *
     * @param string $route 包含参数标记(<param>)的路由规则
     * @return string 转换后的正则表达式字符串
     */
    private function convertRouteToRegex(string $route): string
    {
        $route = preg_replace('/<([^>]+)>/', '(?<$1>[^/]+)', $route);
        return '#^' . $route . '$#';
    }

    /**
     * 执行路由匹配后的处理动作
     *
     * @param callable|array $action 要执行的动作，可以是回调函数或控制器方法数组
     * @param array $params 传递给动作的参数
     * @return mixed 动作执行结果
     */
    private function executeAction(callable|array $action, array $params): mixed
    {
        if (is_array($action)) {
            $controllerName = $action[0];
            $methodName = $action[1];

            $controller = new $controllerName();
            return call_user_func_array([$controller, $methodName], $params);
        } elseif (is_callable($action)) {
            return call_user_func_array($action, $params);
        }

        return null;
    }
}