<?php

declare(strict_types=1);
// Created: 20150101 - Updated: 20250205
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP;

class PluginNav
{
    private string $pluginsDir;
    private string $cacheFile;
    private int $cacheExpiry = 3600; // Cache expiry in seconds (1 hour)

    public function __construct(?string $baseDir = null)
    {
        Util::elog(__METHOD__);

        $this->pluginsDir = $baseDir ?? dirname(__DIR__) . '/Plugins';
        $this->cacheFile = dirname(__DIR__) . '/cache/plugins.json';
    }

    public function scanPlugins(): array
    {
        Util::elog(__METHOD__);

        // Check if cache exists and is valid
        if ($this->isCacheValid())
        {
            return $this->loadCache();
        }

        // If no valid cache exists, scan directories and create cache
        $navigation = $this->performScan();
        $this->saveCache($navigation);

        return $navigation;
    }

    private function isCacheValid(): bool
    {
        Util::elog(__METHOD__);

        if (!file_exists($this->cacheFile))
        {
            return false;
        }

        // Check if cache is expired
        $cacheTime = filemtime($this->cacheFile);
        if (time() - $cacheTime > $this->cacheExpiry)
        {
            return false;
        }

        // Check if any plugin directory or meta.json has been modified
        $directories = glob($this->pluginsDir . '/*');
        foreach ($directories as $dir)
        {
            if (!is_dir($dir)) continue;

            // Check directory modification time
            if (filemtime($dir) > $cacheTime)
            {
                return false;
            }

            // Check meta.json modification time
            $metaFile = $dir . '/meta.json';
            if (file_exists($metaFile) && filemtime($metaFile) > $cacheTime)
            {
                return false;
            }
        }

        return true;
    }

    private function loadCache(): array
    {
        Util::elog(__METHOD__);

        $cache = json_decode(file_get_contents($this->cacheFile), true);
        if (json_last_error() === JSON_ERROR_NONE)
        {
            return $cache;
        }
        // If cache is corrupted, perform fresh scan
        return $this->performScan();
    }

    private function saveCache(array $data): void
    {
        Util::elog(__METHOD__);

        $cacheDir = dirname($this->cacheFile);
        if (!is_dir($cacheDir))
        {
            mkdir($cacheDir, 0755, true);
        }
        file_put_contents($this->cacheFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function performScan(): array
    {
        Util::elog(__METHOD__);

        $directories = array_filter(glob($this->pluginsDir . '/*'), 'is_dir');
        $plugins = array_map(
            function ($dir)
            {
                $meta = $this->getPluginMeta($dir);
                return [
                    'name' => $meta['name'],
                    'url' => "?plugin=" . basename($dir),
                    'icon' => $meta['icon'],
                    'order' => $meta['order'],
                    'group' => $meta['group']
                ];
            },
            $directories
        );

        // Sort plugins by order
        usort($plugins, function ($a, $b)
        {
            return $a['order'] <=> $b['order'];
        });

        // Group plugins
        $grouped = [];
        $ungrouped = [];
        foreach ($plugins as $plugin)
        {
            if ($plugin['group'])
            {
                if (!isset($grouped[$plugin['group']]))
                {
                    $grouped[$plugin['group']] = [];
                }
                $grouped[$plugin['group']][] = [
                    $plugin['name'],
                    $plugin['url'],
                    $plugin['icon']
                ];
            }
            else
            {
                $ungrouped[] = [
                    $plugin['name'],
                    $plugin['url'],
                    $plugin['icon']
                ];
            }
        }

        // Calculate minimum order for each group
        $groupOrders = [];
        foreach ($grouped as $groupName => $items)
        {
            $minOrder = PHP_INT_MAX;
            foreach ($items as $item)
            {
                foreach ($plugins as $plugin)
                {
                    if ($plugin['name'] === $item[0])
                    {
                        $minOrder = min($minOrder, $plugin['order']);
                        break;
                    }
                }
            }
            $groupOrders[$groupName] = $minOrder;
        }

        // Build navigation items with order information
        $navItems = [];

        // Add grouped plugins with their order
        foreach ($grouped as $groupName => $items)
        {
            $navItems[] = [
                'type' => 'group',
                'order' => $groupOrders[$groupName],
                'content' => [
                    $groupName,
                    $items,
                    'bi bi-collection fw'
                ]
            ];
        }

        // Add ungrouped plugins with their order
        foreach ($ungrouped as $plugin)
        {
            $navItems[] = [
                'type' => 'single',
                'order' => array_values(array_filter($plugins, fn($p) => $p['name'] === $plugin[0]))[0]['order'],
                'content' => $plugin
            ];
        }

        // Sort all items by order
        usort($navItems, fn($a, $b) => $a['order'] <=> $b['order']);

        // Extract final navigation structure
        return array_map(fn($item) => $item['content'], $navItems);
    }

    private function getPluginMeta(string $dir): array
    {
        Util::elog(__METHOD__);

        $metaFile = $dir . '/meta.json';
        if (file_exists($metaFile))
        {
            $meta = json_decode(file_get_contents($metaFile), true);
            return [
                'name' => $meta['name'] ?? basename($dir),
                'icon' => $meta['icon'] ?? 'bi bi-box-seam fw',
                'order' => $meta['order'] ?? 999,
                'group' => $meta['group'] ?? null
            ];
        }
        return [
            'name' => basename($dir),
            'icon' => 'bi bi-box-seam fw',
            'order' => 999,
            'group' => null
        ];
    }
}
