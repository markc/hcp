<?php

declare(strict_types=1);
// lib/php/theme.php 20150101 - 20230604
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP;

use HCP\Util;

class Theme
{
    private string $buf = '';
    //private array $in = [];

    public function __construct(public Object $g)
    {
        elog(__METHOD__);

        $this->g = $g;
    }

    public function __toString(): string
    {
        elog(__METHOD__);

        return $this->buf;
    }

    public function __call(string $name, array $args): string
    {
        elog(__METHOD__ . '() name = ' . $name . ' class = ' . __CLASS__);

        return 'Theme::' . $name . '() not implemented';
    }

    public function log(): string
    {
        elog(__METHOD__);

        $alts = '';
        foreach (util::log() as $lvl => $msg) {
            $alts .= $msg ? '<p class="alert ' . $lvl . '">' . $msg . "</p>\n" : '';
        }

        return $alts;
    }

    public function nav1(): string
    {
        elog(__METHOD__);
        //elog(var_export($this->g->nav1, true));
        $o = '?o=' . $this->g->in['o'];

        return '
      <nav>' . implode('', array_map(function ($n) use ($o) {
            $c = $o === $n[1] ? ' class="active"' : '';

            return '
        <a' . $c . ' href="' . $n[1] . '">' . $n[0] . '</a>';
        }, $this->g->nav1)) . '
      </nav>';
    }

    public function nav12(): string
    {
        elog(__METHOD__);

        $o = '?o=' . $this->g->in['o'];
        $links = '';

        // Get the current role's navigation items
        $role = $this->g->in['r'] ?? 'non'; // Get role from input
        $items = $this->g->nav1[$role] ?? [];

        foreach ($items as $item) {
            if (!is_array($item) || count($item) < 3) {
                continue;
            }

            [$label, $link, $icon] = $item;

            // Handle regular links vs dropdown menus
            if (is_array($link)) {
                // This is a dropdown menu
                $submenu = '';
                foreach ($link as $subitem) {
                    if (!is_array($subitem) || count($subitem) < 3) {
                        continue;
                    }
                    [$sublabel, $sublink, $subicon] = $subitem;
                    $subiconHtml = $subicon ? '<i class="' . htmlspecialchars($subicon) . '"></i> ' : '';
                    $submenu .= '
          <a class="dropdown-item" href="' . htmlspecialchars($sublink) . '">' . $subiconHtml . htmlspecialchars($sublabel) . '</a>';
                }

                $iconHtml = $icon ? '<i class="' . htmlspecialchars($icon) . '"></i> ' : '';
                $links .= '
        <div class="dropdown">
          <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            ' . $iconHtml . htmlspecialchars($label) . '
          </a>
          <div class="dropdown-menu">' . $submenu . '
          </div>
        </div>';
            } else {
                // This is a regular link
                $href = $link;
                if (strpos($href, '?') === false && strpos($href, '/') !== 0) {
                    $href = '/' . $href;
                }

                $c = $o === $href ? ' class="active"' : '';
                $iconHtml = $icon ? '<i class="' . htmlspecialchars($icon) . '"></i> ' : '';

                $links .= '
        <a' . $c . ' href="' . htmlspecialchars($href) . '">' . $iconHtml . htmlspecialchars($label) . '</a>';
            }
        }

        return '
      <nav>' . $links . '
      </nav>';
    }

    public function head(): string
    {
        elog(__METHOD__);

        return '
    <header>
      <h1>
        <a href="' . $this->g->cfg['self'] . '">' . $this->g->out['head'] . '</a>
      </h1>' . $this->g->out['nav1'] . '
    </header>';
    }

    public function main(): string
    {
        elog(__METHOD__);

        return '
    <main>' . $this->g->out['log'] . $this->g->out['main'] . '
    </main>';
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

        return '
    <pre>' . $this->g->out['end'] . '
    </pre>';
    }

    public function html(): string
    {
        elog(__METHOD__);

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
</html>
';
    }

    public static function dropdown(
        array $ary,
        string $name,
        string $sel = '',
        string $label = '',
        string $class = '',
        string $extra = ''
    ): string {
        elog(__METHOD__);

        $opt = $label ? '
                <option value="">' . ucfirst($label) . '</option>' : '';
        $buf = '';
        $c = $class ? ' class="' . $class . '"' : '';
        foreach ($ary as $k => $v) {
            if (!is_array($v)) {
                continue;
            }

            // Convert array elements to query parameters
            $params = [];
            foreach ($v as $key => $value) {
                if (is_array($value)) {
                    continue; // Skip nested arrays
                }
                $params[$key] = $value;
            }

            $value = http_build_query($params);
            $t = str_replace('?t=', '', $value);
            $s = $sel === $t ? ' selected' : '';

            $buf .= '
                        <option value="' . $t . '"' . $s . '>' . htmlspecialchars($params[0] ?? '') . '</option>';
        }

        return '
                      <select' . $c . ' name="' . $name . '" id="' . $name . '"' . $extra . '>' . $opt . $buf . '
                      </select>';
    }
}
