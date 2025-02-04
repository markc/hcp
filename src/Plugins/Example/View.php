<?php

declare(strict_types=1);
// Created: 20250201 - Updated: 20250204
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Example;

use HCP\Util;

class View
{
    public function html(array $output = []): string
    {
        Util::elog(__METHOD__);
        //Util::elog(var_export($output, true));
        extract($output, EXTR_SKIP);

        return '<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>' . $doc . '</title>' . $css . '
    </head>
    <body>' . $head . $log . $main . $foot . $js . '
    </body>
</html>
';
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

        return __METHOD__;
    }

    public function css(array $in = []): string
    {
        Util::elog(__METHOD__);

        return '
        <style>
            body {
                text-align: center;
                width: 60rem;
                margin-left: auto;
                margin-right: auto;
            }
            
            nav, header, main, footer, pre, div {
                border: dashed 1px red;
                margin: 1rem;
                padding: 1rem;
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
            ' . __METHOD__ . ' (Alerts area)
        </div>';
    }

    public function nav1(array $in = []): string
    {
        Util::elog(__METHOD__);

        return __METHOD__;
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
            ' . __METHOD__ . '
            <nav>
                ' . $this->nav1() . ' |
                ' . $this->nav2() . ' |
                ' . $this->nav3() . '
            </nav>
        </header>';
    }

    public function main(array $in = []): string
    {
        Util::elog(__METHOD__);

        return '

        <main>
            ' . __METHOD__ . '
        </main>';
    }

    public function foot(array $in = []): string
    {
        Util::elog(__METHOD__);

        return '

        <footer>
            ' . __METHOD__ . '
        </footer>';
    }

    public function js(array $in = []): string
    {
        Util::elog(__METHOD__);

        return '

        <script>
            document.write("<div>' . addslashes(__METHOD__) . '</div>")
        </script>';
    }
}
