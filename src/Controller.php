<?php

declare(strict_types=1);
// Created: 20150101 - Updated: 20250204
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP;

class Controller
{
    public array $input = [
        'api' => '',
        'domain' => '',
        'format' => 'html',
        'item' => null,
        'log' => '',
        'action' => 'list',
        'plugin' => 'Home',
        'remote' => 'local',
        'theme' => 'TopNav',
        'xhr' => '',
    ];

    public array $output = [
        'doc' => 'NetServa HCP',
        'css' => '',
        'log' => '',
        'nav1' => '',
        'nav2' => '',
        'nav3' => '',
        'head' => 'NetServa HCP',
        'main' => 'Error: missing page!',
        'foot' => 'Copyright (C) 2015-2025 Mark Constable (AGPL-3.0)',
        'js' => '',
    ];

    public ?object $pluginView = null;
    public ?object $currentTheme = null;
    public Theme $baseTheme;

    public function __construct(
        public readonly Config $config
    )
    {
        Util::elog(__METHOD__);
        $this->initSession();
        $this->input = Util::esc($this->input);
        $this->initCsrf();
        $this->baseTheme = new Theme($this);
    }

    protected function initSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
        {
            session_start();
        }

        // Store session values
        Util::ses('plugin');
        Util::ses('action');
        Util::ses('log');
    }

    protected function initCsrf(): void
    {
        if (!isset($_SESSION['csrf_token']))
        {
            $_SESSION['csrf_token'] = Util::random_token(32);
        }
    }

    public function handlePluginAction(string $pluginClass): array
    {
        try
        {
            $plugin = new $pluginClass($this);
            $action = $this->input['action'];
            return $plugin->$action();
        }
        catch (\RuntimeException $e)
        {
            // Access denied or other error - already redirected in Plugin constructor
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function html(): string
    {
        if ($this->input['xhr'])
        {
            return $this->renderXhr();
        }

        // Use plugin view's html() method if available, otherwise fall back to theme rendering
        if ($this->pluginView && method_exists($this->pluginView, 'html'))
        {
            return $this->pluginView->html($this->output);
        }

        // Use current theme if available, otherwise fall back to base theme
        return $this->currentTheme
            ? $this->currentTheme->html($this->output)
            : $this->baseTheme->html();
    }

    protected function renderXhr(): string
    {
        $content = $this->output[$this->input['xhr']]
            ?? $this->output['main']
            ?? '';

        if (!$content)
        {
            return "Error: Content is empty";
        }

        $contentType = match ($this->input['format'])
        {
            'json' => 'application/json',
            'text' => 'text/plain',
            'markdown' => 'text/markdown',
            default => 'text/html'
        };
        header("Content-Type: $contentType");

        return match ($this->input['format'])
        {
            'json' => json_encode($content, JSON_PRETTY_PRINT),
            'text' => preg_replace('/^\h*\v+/m', '', strip_tags($content)),
            'markdown' => preg_replace('/^\h*\v+/m', '', $content),
            default => $content
        };
    }
}
