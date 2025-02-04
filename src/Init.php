<?php

declare(strict_types=1);
// Created: 20150101 - Updated: 20250204
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP;

const DBG = true;

class Init
{
    private Config $config;
    private ?Controller $controller;

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getController(): Controller
    {
        return $this->controller;
    }

    public function __construct(Config $config, ?Controller $controller = null)
    {
        Util::elog(__METHOD__);
        $this->config = $config;
        $this->controller = $controller ?? new Controller($config);
        $this->bootstrap();
    }

    private function bootstrap(): void
    {
        Util::elog(__METHOD__);

        // Get plugin and theme names
        $themeName = Util::ses('theme', $this->controller->input['theme']);
        $pluginName = $this->controller->input['plugin'];

        // Initialize class names
        $pluginViewClass = "HCP\\Plugins\\$pluginName\\View";
        $themeClass = "HCP\\Themes\\$themeName";
        $pluginModelClass = "HCP\\Plugins\\$pluginName\\Model";

        // Set up view hierarchy
        $baseTheme = new Theme($this->controller);
        $activeTheme = class_exists($themeClass)
            ? new $themeClass($this->controller, $baseTheme)
            : $baseTheme;
        $pluginView = class_exists($pluginViewClass)
            ? new $pluginViewClass($this->controller, $activeTheme)  // Pass activeTheme as parent for fallback
            : null;

        // Store theme references
        $this->controller->currentTheme = $activeTheme;
        $this->controller->pluginView = $pluginView;

        // Process plugin
        if (class_exists($pluginModelClass))
        {
            if ($this->controller->input['api'])
            {
                Util::chkapi($this);
            }
            else
            {
                Util::remember($this);
            }

            // Handle plugin action and update main output
            $result = $this->controller->handlePluginAction($pluginModelClass);
            $this->controller->output['main'] = $this->renderPluginResult($result, $pluginView ?? $activeTheme);
        }

        // Process output methods if not XHR request
        if (empty($this->controller->input['xhr']))
        {
            $this->processOutputMethods($pluginView, $activeTheme, $baseTheme);
        }
    }

    private function renderPluginResult(array $result, object $view): string
    {
        Util::elog(__METHOD__);

        $action = $this->controller->input['action'];

        // Handle redirects
        if ($result['redirect'] ?? false)
        {
            Util::relist();
            return '';
        }

        // Render view with result data
        return $view->$action($result);
    }

    private function processOutputMethods(?object $pluginView, object $activeTheme, Theme $baseTheme): void
    {
        Util::elog(__METHOD__);

        // Always populate output array regardless of whether plugin view has html() method
        foreach ($this->controller->output as $key => $default)
        {
            $this->controller->output[$key] = $this->resolveMethod($key, $pluginView, $activeTheme, $baseTheme) ?? $default;
        }
    }

    private function resolveMethod(string $method, ?object $pluginView, object $activeTheme, Theme $baseTheme): ?string
    {
        //Util::elog(__METHOD__ . " method=$method");

        // Try plugin view first
        if ($pluginView && method_exists($pluginView, $method))
        {
            return $pluginView->{$method}();
        }

        // Then try active theme
        if (method_exists($activeTheme, $method))
        {
            return $activeTheme->{$method}();
        }

        // Finally try base theme
        if (method_exists($baseTheme, $method))
        {
            return $baseTheme->{$method}();
        }

        return null;
    }

    public function __toString(): string
    {
        Util::elog(__METHOD__);

        return $this->controller->html();
    }
}
