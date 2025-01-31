<?php

declare(strict_types=1);
// lib/php/theme.php 20150101 - 20230604
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP;

class Theme
{
    private string $buf = '';
    public ?object $themeImpl = null;

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

        // Only check themeImpl if the method doesn't exist in current class
        if (!method_exists($this, $name) && $this->themeImpl && method_exists($this->themeImpl, $name))
        {
            return $this->themeImpl->$name(...$args);
        }

        return 'Theme::' . $name . '() not implemented';
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

    public function foot(): string
    {
        elog(__METHOD__);

        return '
    <footer class="text-center">
      <br>
      <p><em><small>' . $this->g->out['foot'] . '</small></em></p>
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
