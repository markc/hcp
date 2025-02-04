<?php

declare(strict_types=1);

namespace HCP;

class Init
{
    public Config $config;
    private ?object $pluginView = null;     // Plugin specific view
    private ?object $currentTheme = null;   // Current theme implementation
    private Theme $baseTheme;               // Base theme fallback
    public array $input = [
        'api'       => '',
        'domain'    => '',
        'format'    => 'html',
        'item'      => null,
        'log'       => '',
        'action'    => 'list',
        'plugin'    => 'Home',
        'remote'    => 'local',
        'theme'     => 'TopNav',
        'xhr'       => '',
    ];
    public array $output = [
        'doc'   => 'NetServa HCP',
        'css'   => '',
        'log'   => '',
        'nav1'  => '',
        'nav2'  => '',
        'nav3'  => '',
        'head'  => 'NetServa HCP',
        'main'  => 'Error: missing page!',
        'foot'  => 'Copyright (C) 2015-2025 Mark Constable (AGPL-3.0)',
        'js'    => '',
    ];

    public function __construct(Config $config)
    {
        Util::elog(__METHOD__);

        $this->config = $config;
        session_start();

        // Process input parameters
        $this->input = Util::esc($this->input);

        // Initialize session
        if (!isset($_SESSION['csrf_token']))
        {
            $_SESSION['csrf_token'] = Util::random_token(32);
        }

        // Store session values
        Util::ses('plugin');
        Util::ses('action');
        Util::ses('log');

        $theme_name = Util::ses('theme', $this->input['theme']);
        Util::elog("theme=$theme_name");

        // Initialize view hierarchy
        $view_class = "HCP\\Plugins\\{$this->input['plugin']}\\View";
        $theme_class = "HCP\\Themes\\$theme_name";

        Util::elog("view_class=$view_class");
        Util::elog("theme_class=$theme_class");

        // Set up theme hierarchy
        $this->baseTheme = new Theme($this);

        if (class_exists($theme_class))
        {
            $this->currentTheme = new $theme_class($this);
        }

        if (class_exists($view_class))
        {
            $this->pluginView = new $view_class($this);
        }

        // Process plugin
        $plugin_class = "HCP\\Plugins\\{$this->input['plugin']}\\Model";
        if (class_exists($plugin_class))
        {
            $this->input['api'] ? Util::chkapi($this) : Util::remember($this);
            $this->output['main'] = (string) new $plugin_class($this->baseTheme, $this);
        }

        // Process output
        if (empty($this->input['xhr']))
        {
            foreach ($this->output as $key => $default)
            {
                $this->output[$key] = $this->resolveMethod($key) ?? $default;
            }
        }
    }

    private function resolveMethod(string $method): ?string
    {
        Util::elog(__METHOD__ . ' method=' . $method);

        // Try plugin view first
        if ($this->pluginView && method_exists($this->pluginView, $method))
        {
            //Util::elog(__METHOD__ . ' Try plugin view first');
            return $this->pluginView->{$method}();
        }

        // Then try current theme
        if ($this->currentTheme && method_exists($this->currentTheme, $method))
        {
            //Util::elog(__METHOD__ . ' Then try current theme');
            return $this->currentTheme->{$method}();
        }

        // Finally try base theme
        if (method_exists($this->baseTheme, $method))
        {
            //Util::elog(__METHOD__ . ' Finally try base theme');
            return $this->baseTheme->{$method}();
        }

        return null;
    }

    public function __toString(): string
    {
        Util::elog(__METHOD__);

        if ($this->input['xhr'])
        {
            $content = $this->output[$this->input['xhr']] ?? $this->output['main'] ?? '';
            if (!$content)
            {
                return "Error: Content is empty";
            }

            // Set content type and return formatted content
            $content_type = match ($this->input['format'])
            {
                'json' => 'application/json',
                'text' => 'text/plain',
                'markdown' => 'text/markdown',
                default => 'text/html'
            };
            header("Content-Type: $content_type");

            return match ($this->input['format'])
            {
                'json' => json_encode($content, JSON_PRETTY_PRINT),
                'text' => preg_replace('/^\h*\v+/m', '', strip_tags($content)),
                'markdown' => preg_replace('/^\h*\v+/m', '', $content),
                default => $content
            };
        }

        // For full page render, use the same resolution method as partials
        return $this->resolveMethod('html') ?? $this->baseTheme->html();
    }
}
