<?php
namespace App\Dingraia\Plugin;
class Hook {
    /**
     * 钩子数组
     * @var array
     */
    private static array $hooks = [];
    /**
     * 注册钩子
     * @param string $hookName 钩子名称
     * @param callable $callback 回调函数
     * @param int $priority 优先级
     * @return void
     */
    public static function add(string $hookName, callable $callback, int $priority = 10): void
    {
        if (!isset(self::$hooks[$hookName])) {
            self::$hooks[$hookName] = [];
        }
        self::$hooks[$hookName][] = [
            'callback' => $callback,
            'priority' => $priority
        ];
        // 按优先级排序
        usort(self::$hooks[$hookName], function($a, $b) {
            return $a['priority'] - $b['priority'];
        });
    }

    /**
     * 触发钩子
     * @param string $hookName 钩子名称
     * @param array $params 参数数组
     * @return array 钩子执行结果数组
     */
    public static function trigger(string $hookName, array $params = []): array
    {
        $results = [];
        if (!isset(self::$hooks[$hookName])) {
            return $results;
        }
        foreach (self::$hooks[$hookName] as $hook) {
            $result = call_user_func_array($hook['callback'], $params);
            $results[] = $result;
        }
        return $results;
    }

    /**
     * 添加过滤钩子
     * @param string $hookName 钩子名称
     * @param mixed $value 要过滤的值
     * @param array $params 参数数组
     * @return mixed 过滤后的值
     */
    public static function filter(string $hookName, mixed $value, array $params = []): mixed
    {
        if (!isset(self::$hooks[$hookName])) {
            return $value;
        }
        $params = array_merge([$value], $params);
        foreach (self::$hooks[$hookName] as $hook) {
            $value = call_user_func_array($hook['callback'], $params);
            $params[0] = $value;
        }
        return $value;
    }
}

