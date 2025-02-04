<?php

declare(strict_types=1);
// Created: 20150101 - Updated: 20250202
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP;

use HCP\Util;

class Theme
{
    private string $buf = '';
    public ?object $theme = null;
    public array $db;
    protected Init $init;

    public function __construct(Init $init)
    {
        Util::elog(__METHOD__);

        $this->init = $init;
        $this->db = $init->db ?? [];
    }

    public function __toString(): string
    {
        Util::elog(__METHOD__);

        return $this->buf;
    }

    public function html(): string
    {
        Util::elog(__METHOD__);

        extract($this->init->output, EXTR_SKIP);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{$doc}</title>{$css}
  </head>
  <body>{$log}{$nav1}{$head}{$main}{$foot}{$js}
  </body>
</html>
HTML;
    }

    public function log(): string
    {
        Util::elog(__METHOD__);

        $alts = '';
        foreach (util::log() as $lvl => $msg)
        {
            if ($msg)
            {
                $alts .= <<<HTML
<p class="alert {$lvl}">{$msg}</p>

HTML;
            }
        }

        return $alts;
    }

    public function nav1(array $a = []): string
    {
        Util::elog(__METHOD__);

        $a = isset($a[0]) ? $a : util::get_nav($this->init->config->nav1);
        $o = '?plugin=' . $this->init->input['plugin'];
        $t = '?t=' . util::ses('t');

        return implode('', array_map(function ($n) use ($o, $t)
        {
            if (is_array($n[1]))
            {
                return $this->nav_dropdown($n);
            }
            $c = $o === $n[1] || $t === $n[1] ? ' active' : '';
            $i = isset($n[2]) ? "<i class=\"{$n[2]}\"></i> " : '';

            return <<<HTML

            <li class="nav-item{$c}"><a class="nav-link" href="{$n[1]}">{$i}{$n[0]}</a></li>
HTML;
        }, $a));
    }

    public function head(): string
    {
        Util::elog(__METHOD__);

        $head = $this->init->output['head'] ?? '';
        $nav1 = $this->init->output['nav1'] ?? '';
        $self = $this->init->config->self;

        return <<<HTML

    <header>
      <h1>
        <a href="{$self}">{$head}</a>
      </h1>{$nav1}
    </header>
HTML;
    }

    public function main(): string
    {
        Util::elog(__METHOD__);

        $log = $this->init->output['log'] ?? '';
        $main = $this->init->output['main'] ?? '';

        return <<<HTML

    <main>{$log}{$main}
    </main>
HTML;
    }

    public function foot(): string
    {
        Util::elog(__METHOD__);

        $foot = $this->init->output['foot'];

        return <<<HTML
[Theme] {$foot}
HTML;
    }

    public function create(array $input = []): string
    {
        Util::elog(__METHOD__);

        $csrf_token = $_SESSION['csrf_token'] ?? '';
        $content = $input['content'] ?? '';

        return <<<HTML
            <form method="post">
                <input type="hidden" name="csrf_token" value="{$csrf_token}">
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea class="form-control" id="content" name="content">{$content}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
            </form>
HTML;
    }

    public function read(array $data): string
    {
        Util::elog(__METHOD__);

        if (empty($data))
        {
            return '<div class="alert alert-warning">Item not found</div>';
        }

        $id = $data['id'] ?? '0';
        $content = htmlspecialchars($data['content'] ?? '');

        return <<<HTML
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Item #{$id}</h5>
                    <p class="card-text">{$content}</p>
                </div>
            </div>
HTML;
    }

    public function list(array $items): string
    {
        Util::elog(__METHOD__);

        if (empty($items))
        {
            return '<div class="alert alert-info">No items found</div>';
        }

        $list_items = '';
        foreach ($items as $item)
        {
            $plugin = $this->init->input['plugin'];
            $id = $item['id'];
            $updated = $item['updated'];
            $content = htmlspecialchars(substr($item['content'] ?? '', 0, 100));

            $list_items .= <<<HTML
            <a href="?plugin={$plugin}&action=read&item={$id}" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">Item #{$id}</h5>
                    <small>{$updated}</small>
                </div>
                <p class="mb-1">{$content}</p>
            </a>
HTML;
        }

        return <<<HTML
            <div class="list-group">{$list_items}</div>
HTML;
    }

    public function end(): string
    {
        Util::elog(__METHOD__);

        $end = $this->init->output['end'] ?? '';

        return <<<HTML

    <pre>{$end}
    </pre>
HTML;
    }
    /*
    public function css(): string
    {
        Util::elog(__METHOD__);
        return '';
    }

    public function nav2(): string
    {
        Util::elog(__METHOD__);
        return '';
    }

    public function nav3(): string
    {
        Util::elog(__METHOD__);
        return '';
    }

    public function js(): string
    {
        Util::elog(__METHOD__);
        return '';
    }
*/
    public function modal(array $ary): string
    {
        Util::elog(__METHOD__);

        ['id' => $id, 'title' => $title, 'body' => $body, 'action' => $action] = $ary;
        $hidden = $ary['hidden'] ?? '';
        $footer = isset($ary['footer']) ? <<<HTML

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    {$ary['footer']}
                </div>
HTML : '';
        $self = $this->init->config->self;
        $c = $_SESSION['c'];
        $plugin = $this->init->input['o'];
        $i = $this->init->input['i'];

        return <<<HTML

        <div class="modal fade" id="{$id}" tabindex="-1" aria-labelledby="{$id}-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="{$id}-label">{$title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="{$self}">
                        <input type="hidden" name="c" value="{$c}">
                        <input type="hidden" name="plugin" value="{$plugin}">
                        <input type="hidden" name="action" value="{$action}">
                        <input type="hidden" name="i" value="{$i}">{$hidden}
                        <div class="modal-body">{$body}</div>
                        {$footer}
                    </form>
                </div>
            </div>
        </div>
HTML;
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
        Util::elog(__METHOD__);

        $opt = $label ? <<<HTML

                <option value="">{$label}</option>
HTML : '';
        $buf = '';
        $c = $class ? " class=\"{$class}\"" : '';
        foreach ($ary as $k => $v)
        {
            $t = str_replace('?t=', '', (string) $v[1]);
            $s = $sel === $t ? ' selected' : '';
            $buf .= <<<HTML

                        <option value="{$t}"{$s}>{$v[0]}</option>
HTML;
        }

        return <<<HTML

                      <select{$c} name="{$name}" id="{$name}"{$extra}>{$opt}{$buf}
                      </select>
HTML;
    }

    public function nav_dropdown(array $a = []): string
    {
        Util::elog(__METHOD__);

        $o = '?plugin=' . $this->init->input['plugin'];
        $i = isset($a[2]) ? "<i class=\"{$a[2]}\"></i> " : '';

        $dropdownItems = implode('', array_map(function ($n) use ($o)
        {
            $c = $o === $n[1] ? ' active' : '';
            $i = isset($n[2]) ? "<i class=\"{$n[2]}\"></i> " : '';

            return <<<HTML

                <a class="dropdown-item" href="{$n[1]}">{$i}{$n[0]}</a>
HTML;
        }, $a[1]));

        return <<<HTML

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{$i}{$a[0]}</a>
              <div class="dropdown-menu">{$dropdownItems}
              </div>
            </li>
HTML;
    }
}
