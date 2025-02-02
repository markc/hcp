<?php

declare(strict_types=1);
// src/Plugins/Example/View.php 20250201 - 20250201
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Example;

use HCP\Util;
use HCP\Themes\TopNav;

class View
{
    public $theme;
    public object $g;

    public function __construct(object $g)
    {
        $this->g = $g;
    }

    // The Final HTML Layout

    public function html(): string
    {
        elog(__METHOD__);

        extract($this->g->out, EXTR_SKIP);

        return '<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>' . $doc . '</title>
        <style>' . $css . '</style>
    </head>
    <body>
        <nav>' . $nav1 . ' || ' . $nav2 . ' || ' . $nav3 . '</nav>
        <header>' . $head . '</header>
        <main>' . $main . '</main>
        <footer>' . $foot . '</footer>
        <pre>' . $end . '</pre>
        <script>' . $js . '</script>
        <div>' . $modal . '</div>
        <div>' . $json . '</div>
    </body>
</html>
';
    }

    // Plugin Actions Views

    public function create(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }

    public function read(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }

    public function update(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }

    public function delete(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }

    public function list(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }

    // HTML Partial Views

    public function doc(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }

    public function css(array $in = []): string
    {
        elog(__METHOD__);

        return '
        body {text-align:center;width:60rem;margin-left:auto;margin-right:auto;}
        nav,header,main,footer,pre,div {border:dashed 1px red;margin:1rem;padding:1rem;}
        ';
    }

    public function log(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }

    public function nav1(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }

    public function nav2(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }

    public function nav3(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }

    public function head(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }

    public function main(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }
    /*
    public function foot(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }
    */
    public function js(array $in = []): string
    {
        elog(__METHOD__);

        return 'document.write("' . addslashes(__METHOD__) . '")';
    }

    public function json(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }

    public function modal(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }

    public function end(array $in = []): string
    {
        elog(__METHOD__);

        return __METHOD__;
    }
}
