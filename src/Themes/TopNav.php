<?php

declare(strict_types=1);
// Created: 20250101 - Updated: 20250202
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Themes;

use HCP\Util;
use HCP\Theme;

class TopNav extends Theme
{
    public function html(array $output = []): string
    {
        Util::elog(__METHOD__);

        extract($output, EXTR_SKIP);

        return '<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no maximum-scale=1">
        <title>' . $doc . '</title>' . $css . '
    </head>
    <body>' . $head . $log . $main . $foot . $js . '
    </body>
</html>';
    }

    public function css(): string
    {
        Util::elog(__METHOD__);

        return '
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
        <style>
            body{
                min-height: 75rem;
                padding-top: 5rem;
                overflow-x: hidden;
            }
            table,form {
                width: 100%;
            }
            table.dataTable {
                border-collapse: collapse !important;
            }
            .media {
                flex-direction: column;
                align-items: center;
                margin-top: 1.5rem;
                margin-bottom: 1.5rem;
            }
            .media-img {
                display: flex;
                flex-direction: column;
                align-items: center;
                margin-bottom: 0.75rem;
            }
            .media img {
                max-width: 100%;
                height: auto;
            }
            .media-blank {
                width: 300px;
            }
            .media-title {
                margin-bottom: 0.75rem;
            }
            .alert pre {
                margin: 0;
            }
            .columns {
                column-gap:1.5em;
                columns:1;
            }

            @media (min-width:768px) {
                .columns {
                    column-gap: 1.5em;
                    columns: 2;
                }
                .media {
                    flex-direction: row;
                    align-items: flex-start;
                }
                .media-body {
                    margin-left: 1.5rem;
                }
                .media-img, .media-blank, .media img {
                    max-width: 200px;
                }
            }

            @media (min-width:992px) {
                .media-img, .media-blank, .media img {
                    max-width: 100%;
                }
            }

            @media (min-width:1200px) {
                .columns {
                    column-gap: 1.5em;
                    columns: 3;
                }
                .media-title {
                    display: flex;
                    flex-direction: row;
                    justify-content: space-between;
                    align-items: flex-end;
                }
            }
        </style>';
    }

    public function head(): string
    {
        Util::elog(__METHOD__);

        return '
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
            <div class="container">
                <a class="navbar-brand" href="' . $this->controller->config->self . '">
                    <b><i class="bi bi-server" aria-hidden="true"></i> ' . $this->controller->output['head'] . '</b>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsDefault" aria-controls="navbarsDefault" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarsDefault">
                    <ul class="navbar-nav me-auto">' . $this->controller->output['nav1'] . '</ul>
                    <ul class="navbar-nav ms-auto">' . $this->controller->output['nav3'] . '</ul>
                </div>
            </div>
        </nav>';
    }

    public function nav1(array $a = []): string
    {
        Util::elog(__METHOD__);

        $a = isset($a[0]) ? $a : Util::get_nav($this->controller->config->nav1);
        $o = '?plugin=' . $this->controller->input['plugin'];
        $t = '?theme=' . Util::ses('theme');

        return implode('', array_map(
            fn($n) =>
            is_array($n[1])
                ? $this->nav_dropdown($n)
                : $this->nav_link($n, $o, $t),
            $a
        ));
    }

    private function nav_link(array $n, string $o, string $t): string
    {
        $active = $o === $n[1] || $t === $n[1] ? ' active' : '';
        $icon = $n[2] ?? '' ? '<i class="' . $n[2] . '" aria-hidden="true"></i> ' : '';

        return '
            <li class="nav-item' . $active . '"><a class="nav-link' . $active . '" href="' . $n[1] . '">' . $icon . $n[0] . '</a></li>';
    }

    public function nav2(): string
    {
        Util::elog(__METHOD__);

        return $this->nav_dropdown(['Theme', $this->controller->config->nav2, 'bi bi-grid']);
    }

    public function nav3(): string
    {
        Util::elog(__METHOD__);

        if (!Util::is_usr())
        {
            return '';
        }

        $usr = [
            ['Change Profile', '?plugin=Accounts&action=read&i=' . $_SESSION['usr']['id'], 'bi bi-person'],
            ['Change Password', '?plugin=Auth&action=update&i=' . $_SESSION['usr']['id'], 'bi bi-key'],
            ['Sign out', '?plugin=Auth&action=delete', 'bi bi-box-arrow-right']
        ];

        if (Util::is_adm() && !Util::is_acl(0))
        {
            $usr[] = ['Switch to sysadm', '?plugin=Accounts&action=switch_user&i=' . $_SESSION['adm'], 'bi bi-person-gear'];
        }

        return $this->nav_dropdown([$_SESSION['usr']['login'], $usr, 'bi bi-person-circle']);
    }

    public function nav_dropdown(array $a = []): string
    {
        Util::elog(__METHOD__);

        $o = '?plugin=' . $this->controller->input['plugin'];
        $icon = $a[2] ?? '';
        $iconHtml = $icon ? '<i class="' . $icon . '" aria-hidden="true"></i> ' : '';
        $dropdownItems = implode('', array_map(fn($n) => $this->dropdown_item($n, $o), $a[1]));

        return '
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $iconHtml . $a[0] . '</a>
                        <div class="dropdown-menu">' . $dropdownItems . '</div>
                    </li>';
    }

    private function dropdown_item(array $n, string $o): string
    {
        $active = $o === $n[1] ? ' active' : '';
        $icon = $n[2] ?? '' ? '<i class="' . $n[2] . '" aria-hidden="true"></i> ' : '';

        return '
                        <a class="dropdown-item' . $active . '" href="' . $n[1] . '">' . $icon . $n[0] . '</a>';
    }

    public function main(): string
    {
        Util::elog(__METHOD__);

        return '

        <main class="container px-3">' . $this->controller->output['main'] . '
        </main>';
    }

    public function foot(): string
    {
        Util::elog(__METHOD__);

        return '

        <footer class="text-center p-4">
            [TopNav] ' . $this->controller->output['foot'] . '
        </footer>';
    }

    public function js(): string
    {
        Util::elog(__METHOD__);

        return '

        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>';
    }

    public function modal(array $ary): string
    {
        Util::elog(__METHOD__);

        ['id' => $id, 'title' => $title, 'body' => $body, 'action' => $action] = $ary;
        $hidden = $ary['hidden'] ?? '';
        $footer = isset($ary['footer']) ? '
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    ' . $ary['footer'] . '
                </div>' : '';

        return '
            <div class="modal fade" id="' . $id . '" tabindex="-1" aria-labelledby="' . $id . '-label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="' . $id . '-label">' . $title . '</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post" action="' . $this->controller->config->self . '">
                            <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
                            <input type="hidden" name="plugin" value="' . $this->controller->input['o'] . '">
                            <input type="hidden" name="action" value="' . $action . '">
                            <input type="hidden" name="i" value="' . $this->controller->input['i'] . '">' . $hidden . '
                            <div class="modal-body">' . $body . '</div>
                            ' . $footer . '
                        </form>
                    </div>
                </div>
            </div>';
    }
}
