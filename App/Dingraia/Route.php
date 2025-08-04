<?php

namespace App\Dingraia;
use Closure;

/**
 * 路由管理器，处理HTTP请求并将其分发到对应的控制器方法，支持中间件
 */
class Route
{
    /**
     * 存储注册的路由信息
     * 格式: [路径 => [请求方法 => ['controller' => 处理动作, 'middleware' => 中间件数组]]
     * @var array
     */
    public array $routes = [];

    /**
     * 全局中间件，将应用于所有路由
     * @var array
     */
    private array $globalMiddleware = [];

    /**
     * 当前正在处理的路由参数
     * @var array
     */
    private array $currentParams = [];

    /**
     * 当前匹配的路由动作
     * @var callable|array|null
     */
    private $currentAction = null;

    /**
     * 注册GET请求的路由
     *
     * @param string|array $path 路由路径
     * @param callable|array $action 路由匹配时执行的动作
     * @param array $middleware 该路由使用的中间件
     * @return $this 返回当前Route实例，支持链式调用
     */
    public function get(string|array $path, callable|array $action, array $middleware = []): static
    {
        return $this->map(['GET'], $path, $action, $middleware);
    }

    /**
     * 注册支持多种HTTP请求方法的路由
     *
     * @param string|array $methods 支持的HTTP请求方法数组，如['GET', 'POST']
     * @param string|array $paths 路由路径
     * @param callable|array $action 路由匹配时执行的动作，可以是回调函数或控制器方法数组
     * @param array $middleware 该路由使用的中间件
     * @return $this 返回当前Route实例，支持链式调用
     */
    public function map(string|array $methods, string|array $paths, callable|array $action, array $middleware = []): static
    {
        $methods = array_map('strtoupper', is_string($methods) ? [$methods] : $methods);
        $paths = is_string($paths) ? [$paths] : $paths;

        foreach ($paths as $path) {
            if (mb_substr($path, -1, 1, 'UTF-8') != '/') {
                $path = $path . '/';
            }
            foreach ($methods as $method) {
                $this->routes[$path][$method] = [
                    'action' => $action,
                    'middleware' => $middleware
                ];
            }
        }

        return $this;
    }

    /**
     * 注册POST请求的路由
     *
     * @param string|array $path 路由路径
     * @param callable|array $action 路由匹配时执行的动作
     * @param array $middleware 该路由使用的中间件
     * @return $this 返回当前Route实例，支持链式调用
     */
    public function post(string|array $path, callable|array $action, array $middleware = []): static
    {
        return $this->map(['POST'], $path, $action, $middleware);
    }

    /**
     * 注册PUT请求的路由
     *
     * @param string $path 路由路径
     * @param callable|array $action 路由匹配时执行的动作
     * @param array $middleware 该路由使用的中间件
     * @return $this 返回当前Route实例，支持链式调用
     */
    public function put(string $path, callable|array $action, array $middleware = []): static
    {
        return $this->map(['PUT'], $path, $action, $middleware);
    }

    /**
     * 注册DELETE请求的路由
     *
     * @param string $path 路由路径
     * @param callable|array $action 路由匹配时执行的动作
     * @param array $middleware 该路由使用的中间件
     * @return $this 返回当前Route实例，支持链式调用
     */
    public function delete(string $path, callable|array $action, array $middleware = []): static
    {
        return $this->map(['DELETE'], $path, $action, $middleware);
    }

    /**
     * 注册支持所有HTTP请求方法的路由
     *
     * @param string $path 路由路径
     * @param callable|array $action 路由匹配时执行的动作
     * @param array $middleware 该路由使用的中间件
     * @return $this 返回当前Route实例，支持链式调用
     */
    public function any(string $path, callable|array $action, array $middleware = []): static
    {
        return $this->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'], $path, $action, $middleware);
    }

    /**
     * 添加全局中间件，将应用于所有路由
     *
     * @param string|array $middleware 中间件类名或数组
     * @return $this
     */
    public function middleware(string|array $middleware): static
    {
        $this->globalMiddleware = array_merge(
            $this->globalMiddleware,
            is_array($middleware) ? $middleware : [$middleware]
        );

        return $this;
    }

    /**
     * 解析当前HTTP请求并执行匹配的路由动作，包括中间件处理
     *
     * @return mixed 路由处理结果
     */
    public function resolve(): mixed
    {
        $requestMethod = $this->getRequestMethod();
        $requestUri = $_SERVER['REQUEST_URI'];
        if (mb_substr($requestUri, -1, 1, 'UTF-8') != '/') {
            $requestUri = $requestUri . '/';
        }
        $path = strtok($requestUri, '?');
        $path = preg_replace('#^/index\.php#', '', $path);
        $path = $path ?: '/';

        foreach ($this->routes as $routePath => $methods) {
            $params = [];
            if ($this->matchRoute($routePath, $path, $params)) {
                if (isset($methods[$requestMethod])) {
                    $this->currentParams = $params;
                    $this->currentAction = $methods[$requestMethod]['action'];
                    $middleware = array_merge(
                        $this->globalMiddleware,
                        $methods[$requestMethod]['middleware']
                    );
                    return $this->runMiddlewarePipeline($middleware);
                }
            }
        }
        $this->currentAction = ['ErrorController', 'notFound'];
        $this->currentParams = [];

        return $this->runMiddlewarePipeline($this->globalMiddleware);
    }

    /**
     * 运行中间件管道
     *
     * @param array $middleware 中间件数组
     * @return mixed
     */
    private function runMiddlewarePipeline(array $middleware): Closure
    {
        // 创建一个闭包作为管道的最终处理函数（执行路由动作）
        $next = function () {
            return $this->executeAction($this->currentAction, $this->currentParams);
        };

        // 反转中间件数组，从最后一个开始构建管道
        $middleware = array_reverse($middleware);

        // 构建中间件管道
        foreach ($middleware as $middlewareClass) {
            $next = function () use ($middlewareClass, $next) {
                // 实例化中间件并调用handle方法
                $instance = new $middlewareClass();
                return $instance->handle($next);
            };
        }

        // 执行管道
        return $next();
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
