<?php
namespace App\Dingraia\Plugin;
class PluginManager {
    private string $pluginsDir;
    private array $plugins = [];
    private array $activePlugins = [];
    public function __construct($pluginsDir = 'plugins/') {
        $this->pluginsDir = rtrim($pluginsDir, '/') . '/';
    }
    /**
     * 加载所有插件
     * @return array
     */
    public function getAllPlugins(): array
    {
        if (!is_dir($this->pluginsDir)) {
            return [];
        }
        $pluginDirs = scandir($this->pluginsDir);
        foreach ($pluginDirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            $pluginFile = $this->pluginsDir . $dir . '/' . $dir . '.php';
            if (is_file($pluginFile)) {
                require_once $pluginFile;
                $className = ucfirst($dir);
                if (class_exists($className) && in_array('PluginInterface', class_implements($className))) {
                    $this->plugins[$dir] = new $className();
                }
            }
        }
        return $this->plugins;
    }
    public function getPluginFromCache()
    {

    }
    /**
     * 激活插件
     * @param string $pluginName
     * @return bool
     */
    public function activatePlugin(string $pluginName): bool
    {
        if (isset($this->plugins[$pluginName])) {
            $result = $this->plugins[$pluginName]->activate();

            if ($result) {
                $this->activePlugins[] = $pluginName;
                $this->saveActivePlugins();
                return true;
            }
        }

        return false;
    }

    // 禁用插件
    public function deactivatePlugin(string $pluginName): bool
    {
        if (isset($this->plugins[$pluginName])) {
            $result = $this->plugins[$pluginName]->deactivate();

            if ($result) {
                $key = array_search($pluginName, $this->activePlugins);
                if ($key !== false) {
                    unset($this->activePlugins[$key]);
                    $this->saveActivePlugins();
                }
                return true;
            }
        }

        return false;
    }

    // 保存激活的插件列表
    private function saveActivePlugins() {
        // 实际应用中应该保存到数据库或配置文件
        // file_put_contents('active_plugins.json', json_encode($this->activePlugins));
    }

    // 初始化激活的插件
    public function initActivePlugins(): void
    {
        foreach ($this->activePlugins as $pluginName) {
            if (isset($this->plugins[$pluginName])) {
                // 插件可以在这里注册自己的钩子
                $this->plugins[$pluginName]->init();
            }
        }
    }

    // 获取所有插件
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    // 获取激活的插件
    public function getActivePlugins(): array
    {
        return $this->activePlugins;
    }
}

