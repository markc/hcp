<?php

declare(strict_types=1);
// Created: 20150101 - Updated: 20250202
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP;

class Theme
{
    private string $buf = '';
    public ?object $theme = null;
    public object $g;

    public function __construct(object $g)
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

        // First try theme implementation if available
        if ($this->theme && method_exists($this->theme, $name))
        {
            return $this->theme->$name(...$args);
        }

        // Then try this class's methods
        if (method_exists($this, $name))
        {
            return $this->$name(...$args);
        }

        return 'Theme::' . $name . '() not implemented';
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
    <title>' . $doc . '</title>' . $css . '
  </head>
  <body>' . $log . $nav1 . $head . $main . $foot . $js . '
  </body>
</html>
';
    }

    public function log(): string
    {
        elog(__METHOD__);

        $alts = '';
        foreach (util::log() as $lvl => $msg)
        {
            $alts .= $msg ? '<p class="alert ' . $lvl . '">' . $msg . "</p>\n" : '';
        }

        return $alts;
    }

    public function nav1(array $a = []): string
    {
        elog(__METHOD__);

        $a = isset($a[0]) ? $a : util::get_nav($this->g->nav1);
        $o = '?o=' . $this->g->in['o'];
        $t = '?t=' . util::ses('t');

        return implode('', array_map(function ($n) use ($o, $t)
        {
            if (is_array($n[1]))
            {
                return $this->nav_dropdown($n);
            }
            $c = $o === $n[1] || $t === $n[1] ? ' active' : '';
            $i = isset($n[2]) ? '<i class="' . $n[2] . '"></i> ' : '';

            return '
            <li class="nav-item' . $c . '"><a class="nav-link" href="' . $n[1] . '">' . $i . $n[0] . '</a></li>';
        }, $a));
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
    /*
    public function foot(): string
    {
        elog(__METHOD__);

        return '[Theme] ' . $this->g->out['foot'];
    }
    */
    public function end(): string
    {
        elog(__METHOD__);

        return '
    <pre>' . $this->g->out['end'] . '
    </pre>';
    }

    public function css(): string
    {
        elog(__METHOD__);
        return '';
    }

    public function nav2(): string
    {
        elog(__METHOD__);
        return '';
    }

    public function nav3(): string
    {
        elog(__METHOD__);
        return '';
    }

    public function js(): string
    {
        elog(__METHOD__);
        return '';
    }

    public function modal(array $ary): string
    {
        elog(__METHOD__);

        ['id' => $id, 'title' => $title, 'body' => $body, 'action' => $action] = $ary;
        $hidden = $ary['hidden'] ?? '';
        $footer = isset($ary['footer']) ? sprintf('
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    %s
                </div>', $ary['footer']) : '';

        return sprintf(
            '
        <div class="modal fade" id="%1$s" tabindex="-1" aria-labelledby="%1$s-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="%1$s-label">%2$s</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="%3$s">
                        <input type="hidden" name="c" value="%4$s">
                        <input type="hidden" name="o" value="%5$s">
                        <input type="hidden" name="m" value="%6$s">
                        <input type="hidden" name="i" value="%7$s">%8$s
                        <div class="modal-body">%9$s</div>
                        %10$s
                    </form>
                </div>
            </div>
        </div>',
            $id,
            $title,
            $this->g->cfg['self'],
            $_SESSION['c'],
            $this->g->in['o'],
            $action,
            $this->g->in['i'],
            $hidden,
            $body,
            $footer
        );
    }

    public static function dropdown(
        array $ary,
        string $name,
        string $sel = '',
        string $label = '',
        string $class = '',
        string $extra = ''
    ): string
    {
        elog(__METHOD__);

        $opt = $label ? '
                <option value="">' . ucfirst($label) . '</option>' : '';
        $buf = '';
        $c = $class ? ' class="' . $class . '"' : '';
        foreach ($ary as $k => $v)
        {
            $t = str_replace('?t=', '', (string) $v[1]);
            $s = $sel === $t ? ' selected' : '';
            $buf .= '
                        <option value="' . $t . '"' . $s . '>' . $v[0] . '</option>';
        }

        return '
                      <select' . $c . ' name="' . $name . '" id="' . $name . '"' . $extra . '>' . $opt . $buf . '
                      </select>';
    }

    public function nav_dropdown(array $a = []): string
    {
        elog(__METHOD__);

        $o = '?o=' . $this->g->in['o'];
        $i = isset($a[2]) ? '<i class="' . $a[2] . '"></i> ' : '';

        return '
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $i . $a[0] . '</a>
              <div class="dropdown-menu">' . implode('', array_map(function ($n) use ($o)
        {
            $c = $o === $n[1] ? ' active' : '';
            $i = isset($n[2]) ? '<i class="' . $n[2] . '"></i> ' : '';

            return '
                <a class="dropdown-item" href="' . $n[1] . '">' . $i . $n[0] . '</a>';
        }, $a[1])) . '
              </div>
            </li>';
    }
}
