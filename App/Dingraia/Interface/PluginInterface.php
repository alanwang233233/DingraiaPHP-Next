<?php
namespace App\Dingraia\Interface;
interface PluginInterface {
    public function getInfo(): array;
    public function activate(): bool;
    public function deactivate(): bool;
    public function install(): bool;
    public function uninstall(): bool;
    public function main();
}