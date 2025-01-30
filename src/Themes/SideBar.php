<?php

declare(strict_types=1);

namespace HCP\Themes;

use HCP\Theme;

class SideBar extends Theme
{
    public object $g;

    public function __construct(object $g)
    {
        elog(__METHOD__);
        $this->g = $g;
    }

    public function list(array $in = []): string
    {
        elog(__METHOD__);

        $currentPage = $this->g->in['o'] ?? '';
        $items = [
            ['Home', '?o=Home', 'bi-house'],
            ['Accounts', '?o=Accounts', 'bi-people'],
            ['Processes', '?o=Processes', 'bi-cpu'],
            ['Info System', '?o=InfoSys', 'bi-info-circle']
        ];

        $html = '<div class="sidebar-sticky">
            <ul class="nav flex-column">';

        foreach ($items as [$label, $url, $icon]) {
            $isActive = $currentPage === strtolower(str_replace(['?o=', ' '], '', $url));
            $html .= sprintf(
                '<li class="nav-item">
                    <a class="nav-link%s" href="%s">
                        <i class="%s"></i>
                        %s
                    </a>
                </li>',
                $isActive ? ' active' : '',
                $url,
                $icon,
                $label
            );
        }

        $html .= '</ul></div>';

        return $html;
    }

    public function create(array $in): string
    {
        elog(__METHOD__);
        return '';
    }

    public function read(array $in): string
    {
        elog(__METHOD__);
        return '';
    }

    public function render(string $content, array $in = []): string
    {
        return '
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                    ' . $this->list($in) . '
                </div>
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    ' . $content . '
                </main>
            </div>
        </div>';
    }

    public function css(): string
    {
        elog(__METHOD__);
        return '
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { padding-top: 5rem; }
    .sidebar {
      position: fixed;
      top: 56px;
      bottom: 0;
      left: 0;
      z-index: 100;
      padding: 48px 0 0;
      box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
    }
    .sidebar-sticky {
      position: relative;
      top: 0;
      height: calc(100vh - 48px);
      padding-top: .5rem;
      overflow-x: hidden;
      overflow-y: auto;
    }
    .sidebar .nav-link {
      font-weight: 500;
      color: #333;
      padding: .75rem 1rem;
    }
    .sidebar .nav-link i {
      margin-right: .5rem;
      color: #727272;
    }
    .sidebar .nav-link.active {
      color: #2470dc;
    }
    .sidebar .nav-link:hover {
      color: #2470dc;
    }
    .sidebar .nav-link.active i {
      color: #2470dc;
    }
  </style>';
    }

    public function js(): string
    {
        elog(__METHOD__);
        return '
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>';
    }

    public function head(): string
    {
        elog(__METHOD__);
        return '';
    }

    public function main(): string
    {
        elog(__METHOD__);
        return sprintf(
            '<main class="container-fluid">
                <div class="row">%s%s</div>
            </main>',
            $this->g->out['log'] ?? '',
            $this->g->out['main'] ?? ''
        );
    }

    public function foot(): string
    {
        elog(__METHOD__);
        return '
    <footer class="text-center">
      <br>
      <p><em><small>Copyright (C) 2015-2025 Mark Constable (AGPL-3.0)</small></em></p>
    </footer>';
    }

    public function end(): string
    {
        elog(__METHOD__);
        return '';
    }

    public function html(): string
    {
        elog(__METHOD__);

        // Set required output variables if not already set
        $this->g->out['doc'] = $this->g->out['doc'] ?? $this->g->out['head'] ?? 'HCP';
        $this->g->out['css'] = $this->g->out['css'] ?? $this->css();
        $this->g->out['js'] = $this->g->out['js'] ?? $this->js();
        $this->g->out['head'] = $this->g->out['head'] ?? $this->head();
        $this->g->out['main'] = $this->g->out['main'] ?? $this->main();
        $this->g->out['foot'] = $this->g->out['foot'] ?? $this->foot();
        $this->g->out['end'] = $this->g->out['end'] ?? $this->end();

        extract($this->g->out, EXTR_SKIP);

        return '<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>' . $doc . '</title>' . $css . $js . '
  </head>
  <body>' . $head . $main . $foot . $end . '
  </body>
</html>';
    }
}
