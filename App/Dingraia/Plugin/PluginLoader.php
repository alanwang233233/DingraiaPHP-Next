<?php

namespace App\Dingraia\Plugin;

use Composer\Autoload\ClassLoader;

class PluginClassLoader
{
    private $loader;
    private $pluginDir;
    private $enabledPlugins = [];

    public function __construct(ClassLoader $loader, string $pluginDir)
    {
        $this->loader = $loader;
        $this->pluginDir = $pluginDir;
        $this->loadEnabledPlugins();
        $this->register();
    }

    private function loadEnabledPlugins()
    {
        $plugins = \Models\Plugin::where('status', 1)->get();
        foreach ($plugins as $plugin) {
            $this->enabledPlugins[$plugin->name] = true;
        }
    }

    private function register()
    {
        $this->loader->add('Plugin\\', $this->pluginDir);
        // 注册一个预处理函数，过滤禁用的插件
        $this->loader->register(true);
        // 注册一个自定义的类加载回调
        spl_autoload_register([$this, 'loadClass'], true, true);
    }

    public function loadClass($class)
    {
        // 检查是否是插件类
        if (str_starts_with($class, 'Plugin\\')) {
            // 提取插件名称
            $parts = explode('\\', $class);
            $pluginName = $parts[1] ?? '';

            // 如果插件被禁用，则不加载
            if (!isset($this->enabledPlugins[$pluginName])) {
                return false;
            }
        }

        // 使用默认加载器
        return $this->loader->loadClass($class);
    }
}