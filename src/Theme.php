<?php

declare(strict_types=1);
// Created: 20150101 - Updated: 20250204
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP;

class Theme
{
    protected string $buf = '';
    protected ?Theme $parentTheme;

    public function __construct(
        protected readonly Controller $controller,
        ?Theme $parentTheme = null
    )
    {
        Util::elog(__METHOD__);

        $this->parentTheme = $parentTheme;
    }

    public function html(): string
    {
        Util::elog(__METHOD__);

        extract($this->controller->output, EXTR_SKIP);

        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>{$doc}</title>{$css}
            </head>
            <body>{$log}{$nav1}{$head}{$main}{$foot}{$js}
            </body>
        </html>
        HTML;
    }

    public function nav1(): string
    {
        Util::elog(__METHOD__);

        $nav = Util::get_nav($this->controller->config->nav1);
        return implode('', array_map(fn($n) => $this->nav_item($n), $nav));
    }

    public function nav_item(array $n): string
    {
        if (is_array($n[1]))
        {
            return $this->nav_dropdown($n);
        }

        $o = '?plugin=' . $this->controller->input['plugin'];
        $c = $o === $n[1] ? ' active' : '';
        $i = $n[2] ?? '';
        $i = $i ? "<i class=\"{$i}\"></i> " : '';

        return <<<HTML
       <li class="nav-item{$c}"><a class="nav-link" href="{$n[1]}">{$i}{$n[0]}</a></li>
       HTML;
    }

    public function nav_dropdown(array $a): string
    {
        Util::elog(__METHOD__);

        $i = $a[2] ?? '';
        $i = $i ? "<i class=\"{$i}\"></i> " : '';

        $items = implode('', array_map(fn($n) => $this->nav_item($n), $a[1]));

        return <<<HTML
       <li class="nav-item dropdown">
         <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">{$i}{$a[0]}</a>
         <div class="dropdown-menu">{$items}</div>
       </li>
       HTML;
    }

    public function list(array $items): string
    {
        Util::elog(__METHOD__);

        if (empty($items))
        {
            return '<div class="alert">No items found</div>';
        }

        $list = '';
        foreach ($items as $item)
        {
            $id = $item['id'];
            $updated = $item['updated'];
            $content = htmlspecialchars($item['content'] ?? '');
            $plugin = $this->controller->input['plugin'];

            $list .= <<<HTML
           <a href="?plugin={$plugin}&action=read&item={$id}" class="list-group-item">
               <h5>Item #{$id}</h5>
               <p>{$content}</p>
               <small>{$updated}</small>
           </a>
           HTML;
        }

        return "<div class='list-group'>$list</div>";
    }

    public function create(array $input): string
    {
        Util::elog(__METHOD__);

        $csrf = $_SESSION['csrf_token'] ?? '';
        $content = $input['content'] ?? '';

        return <<<HTML
       <form method="post">
           <input type="hidden" name="csrf_token" value="{$csrf}">
           <textarea name="content">{$content}</textarea>
           <button type="submit">Create</button>
       </form>
       HTML;
    }

    public function read(array $data): string
    {
        Util::elog(__METHOD__);

        if (empty($data))
        {
            return '<div class="alert">Item not found</div>';
        }

        $id = $data['id'] ?? '0';
        $content = htmlspecialchars($data['content'] ?? '');

        return <<<HTML
       <div class="card">
           <div class="card-body">
               <h5>Item #{$id}</h5>
               <p>{$content}</p>
           </div>
       </div>
       HTML;
    }

    public function __toString(): string
    {
        Util::elog(__METHOD__);

        return $this->buf;
    }
}
