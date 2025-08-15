<?php
namespace App\Dingraia\Plugin;
class PluginLoader {
    /**
     * 插件目录
     * @var string
     */
    private string $pluginDir = APP_PATH . 'Plugin/';
    /**
     * 插件列表
     * @var array
     */
    private array $pluginList = [];
    /**
     * 加载插件
     * @return void
     */
    public function load(): void
    {
        $pluginDir = $this->pluginDir;
        if (!is_dir($pluginDir)) {
            return;
        }
        $pluginList = scandir($pluginDir);
        foreach ($pluginList as $plugin) {
            if ($plugin == '.' || $plugin == '..') {
                continue;
            }
            $pluginPath = $pluginDir . $plugin;
            if (!is_dir($pluginPath)) {
                continue;
            }
            $this->pluginList[] = $plugin;
        }
    }
}