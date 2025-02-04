<?php

declare(strict_types=1);
// Created: 20250101 - Updated: 20250202
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Themes;

use HCP\Util;
use HCP\Theme;

class SideBar extends Theme
{
    public function __construct(object $g)
    {
        Util::elog(__METHOD__);

        parent::__construct($g);
    }

    public function list(array $in = []): string
    {
        Util::elog(__METHOD__);

        $lhsNav = $this->renderPluginNav($this->g->input['nav1'] ?? []);
        $rhsNav = $this->renderPluginNav($this->g->input['nav2'] ?? []);

        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
    
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="csrf-token" content="{$_SESSION['csrf_token']}">
            <title>{$this->g->out['doc']}</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
            <link href="assets/css/pablo.css" rel="stylesheet">
        </head>
    
        <body>
            <nav class="navbar navbar-dark bg-dark fixed-top navbar-height">
                <div class="container-fluid">
                    <button class="btn btn-dark" id="leftSidebarToggle" type="button">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <a class="navbar-brand" href="/">
                        {$this->g->out['doc']}
                    </a>
                    <button class="btn btn-dark" id="rightSidebarToggle" type="button">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            </nav>
            <div class="sidebar left" id="leftSidebar">
                {$lhsNav}
            </div>
            <div class="sidebar right" id="rightSidebar">
                {$rhsNav}
            </div>
            <div class="main-content" id="main">
                <div class="container-fluid">
                    <main class="content-section" id="content-section">
                        {$this->g->out['main']}
                    </main>
                </div>
            </div>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
            <script src="assets/js/pablo.js"></script>
        </body>
    
        </html>
        HTML;
    }

    public function renderPluginNav(array $navData): string
    {
        if (!isset($navData[0]))
        {
            return '';
        }

        // Since plugins use a fixed structure [section_name, items_array, icon],
        // we treat it as a dropdown
        return $this->renderDropdown(
            [
                $navData[0],  // Section name (e.g., "Plugins")
                $navData[1],  // Array of plugin items
                $navData[2]   // Section icon
            ]
        );
    }

    private function renderDropdown(array $section): string
    {
        $currentPlugin = $this->g->input['object'] ?? 'Home';
        $icon = isset($section[2]) ? '<i class="' . $section[2] . '"></i> ' : '';

        $submenuItems = array_map(
            function ($item) use ($currentPlugin)
            {
                $isActive = strtolower($currentPlugin) === strtolower($item[0]) ? ' active' : '';
                $itemIcon = isset($item[2]) ? '<i class="' . $item[2] . '"></i> ' : '';

                return '
                        <li class="nav-item">
                            <a class="nav-link' . $isActive . '" href="' . $item[1] . '">' .
                    $itemIcon . $item[0] .
                    '</a>
                        </li>';
            },
            $section[1]
        );

        return '
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#' . $section[0] . 'Submenu" 
                   role="button" aria-expanded="false" aria-controls="' . $section[0] . 'Submenu">' .
            $icon . $section[0] . ' <i class="bi bi-chevron-right chevron-icon fw ms-auto"></i>
                </a>
                <div class="collapse submenu" id="' . $section[0] . 'Submenu">
                    <ul class="nav flex-column">' .
            implode('', $submenuItems) . '
                    </ul>
                </div>
            </li>
        </ul>';
    }

    private function renderSingleNav(array $item): string
    {
        $currentPlugin = $this->g->input['object'] ?? 'Home';
        $isActive = $currentPlugin === $item[1] ? ' active' : '';
        $icon = isset($item[2]) ? '<i class="' . $item[2] . '"></i> ' : '';

        return '
        <ul class="nav flex-column">
            <li class="nav-item' . $isActive . '">
                <a class="nav-link" href="' . $item[1] . '">' . $icon . $item[0] . '</a>
            </li>
        </ul>';
    }
}
