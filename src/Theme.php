<?php

declare(strict_types=1);
// Created: 20150101 - Updated: 20250205
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

    public function __toString(): string
    {
        Util::elog(__METHOD__);

        return $this->buf;
    }

    public function html(): string
    {
        Util::elog(__METHOD__);

        extract($this->controller->output, EXTR_SKIP);

        return '<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>' . $doc . '</title>' . $css . '
    </head>
    <body>' . $head . $log . $main . $foot . $js . '
    </body>
</html>';
    }

    // Plugin Actions Views

    public function create(array $in = []): string
    {
        Util::elog(__METHOD__);

        return __METHOD__;
    }

    public function read(array $in = []): string
    {
        Util::elog(__METHOD__);

        return __METHOD__;
    }

    public function update(array $in = []): string
    {
        Util::elog(__METHOD__);

        return __METHOD__;
    }

    public function delete(array $in = []): string
    {
        Util::elog(__METHOD__);

        return __METHOD__;
    }

    public function list(array $in = []): string
    {
        Util::elog(__METHOD__);

        return __METHOD__;
    }

    // HTML Partial Views

    public function doc(array $in = []): string
    {
        Util::elog(__METHOD__);

        return $this->controller->output['doc'];
    }

    public function css(array $in = []): string
    {
        Util::elog(__METHOD__);

        return '
        <style>
            body {
                width: 60rem;
                margin-left: auto;
                margin-right: auto;
            }
            /*
            nav, header, main, footer, pre, div {
                border: dashed 1px red;
                margin: 1rem;
                padding: 1rem;
            }
            */
            footer {
                text-align: center;
            }

            @media screen and (max-width: 768px) {
                body {
                    width: 100%;
                    margin: 0;
                }
                
                nav, header, main, footer, pre, div {
                    width: auto;
                    margin: 1rem;
                }
            }
        </style>';
    }

    public function log(array $in = []): string
    {
        Util::elog(__METHOD__);

        return '

        <div>
            ' . $this->controller->output['log']  . '
        </div>';
    }

    public function nav1(array $in = []): string
    {
        Util::elog(__METHOD__);

        return implode('', array_map(
            fn($n) => is_array($n[1])
                ? '
                <select name="' . $n[0] . '" id="' . $n[0] . '" onchange="if(this.value) window.location.href=this.value">'
                . '<option value="">- ' . $n[0] . ' -</option>'
                . implode('', array_map(fn($v) => '
                    <option value="' . $v[1] . '">' . $v[0] . '</option>', $n[1]))
                . '
                </select>'
                : '
                <a href="' . $n[1] . '">' . $n[0] . '</a>',
            Util::get_nav($this->controller->config->nav1)
        ));
    }

    public function nav2(array $in = []): string
    {
        Util::elog(__METHOD__);

        return __METHOD__;
    }

    public function nav3(array $in = []): string
    {
        Util::elog(__METHOD__);

        return __METHOD__;
    }

    public function head(array $in = []): string
    {
        Util::elog(__METHOD__);

        return '
        <header>
            <h1><a href="?plugin=Home">' . $this->controller->output['doc'] . '</a></h1>
            <nav>' . $this->nav1() . '
            </nav>
        </header>';
    }

    public function main(array $in = []): string
    {
        Util::elog(__METHOD__);

        return '

        <main>
            ' . $this->controller->output['main'] . '
        </main>';
    }

    public function foot(array $in = []): string
    {
        Util::elog(__METHOD__);

        return '

        <footer>
            ' . $this->controller->output['foot'] . '
        </footer>';
    }

    // The original Theme CRUDL methods

    public function list2(array $items): string
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

            $list .= '<a href="?plugin=' . $plugin . '&action=read&item=' . $id . '" class="list-group-item">
               <h5>Item #' . $id . '</h5>
               <p>' . $content . '</p>
               <small>' . $updated . '</small>
           </a>';
        }

        return '<div class="list-group">' . $list . '</div>';
    }

    public function create2(array $input): string
    {
        Util::elog(__METHOD__);

        $csrf = $_SESSION['csrf_token'] ?? '';
        $content = $input['content'] ?? '';

        return '<form method="post">
           <input type="hidden" name="csrf_token" value="' . $csrf . '">
           <textarea name="content">' . $content . '</textarea>
           <button type="submit">Create</button>
       </form>';
    }

    public function read2(array $data): string
    {
        Util::elog(__METHOD__);

        if (empty($data))
        {
            return '<div class="alert">Item not found</div>';
        }

        $id = $data['id'] ?? '0';
        $content = htmlspecialchars($data['content'] ?? '');

        return '<div class="card">
           <div class="card-body">
               <h5>Item #' . $id . '</h5>
               <p>' . $content . '</p>
           </div>
       </div>';
    }
}
