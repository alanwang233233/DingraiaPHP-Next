<?php

namespace App\Dingraia\Plugin;
use App\Dingraia\Interface\PluginInterface;
abstract class SamplePlugin implements PluginInterface {
    public function getName(): string
    {
        return 'SamplePlugin';
    }
    public function getDescription(): string
    {
        return '这是一个示例插件';
    }
    public function activate(): bool
    {
        return true;
    }
    public function deactivate(): bool
    {
        return true;
    }
    public function install(): bool
    {
        return true;
    }
    public function uninstall(): bool
    {
        return true;
    }
    public function getVersion(): string
    {
        return '1.0.0';
    }
    public function getAuthor(): string
    {
        return 'DingraiaPHP';
    }
}