<?php

declare(strict_types=1);

namespace HCP\Plugin;

use HCP\Util;

class PluginManager
{
    private array $loadedPlugins = [];
    private string $pluginNamespace = 'HCP\\Plugins\\';

    public function loadPlugin(string $name, object $theme): ?object
    {
        // Return cached plugin if already loaded
        if (isset($this->loadedPlugins[$name])) {
            return $this->loadedPlugins[$name];
        }

        $modelClass = $this->pluginNamespace . $name . '\\Model';

        // Check if plugin exists
        if (!class_exists($modelClass)) {
            return null;
        }

        // Check authentication
        if (!Util::is_usr() && ('Auth' !== $name || !in_array($theme->g->in['m'], ['list', 'create', 'resetpw']))) {
            Util::redirect($theme->g->cfg['self'] . '?o=Auth');
            return null;
        }

        // Initialize plugin
        try {
            $plugin = new $modelClass($theme);
            $this->loadedPlugins[$name] = $plugin;
            return $plugin;
        } catch (\Exception $e) {
            error_log("Failed to load plugin $name: " . $e->getMessage());
            return null;
        }
    }

    public function getLoadedPlugins(): array
    {
        return $this->loadedPlugins;
    }

    public function hasPlugin(string $name): bool
    {
        return isset($this->loadedPlugins[$name]);
    }

    public function getPlugin(string $name): ?object
    {
        return $this->loadedPlugins[$name] ?? null;
    }

    public function unloadPlugin(string $name): void
    {
        unset($this->loadedPlugins[$name]);
    }

    public function unloadAll(): void
    {
        $this->loadedPlugins = [];
    }
}
