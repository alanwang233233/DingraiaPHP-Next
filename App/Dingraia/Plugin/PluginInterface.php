<?php
namespace App\Dingraia\Plugin;
interface PluginInterface {
    public function getName(): string;
    public function getDescription(): string;
    public function activate(): bool;
    public function deactivate(): bool;
    public function install(): bool;
    public function uninstall(): bool;
}