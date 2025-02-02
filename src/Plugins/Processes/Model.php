<?php

declare(strict_types=1);
// plugins/processes.php 20170225 - 20250128
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Processes;

use HCP\Plugin;
use HCP\Util;

final class Model extends Plugin
{
    public function list(): string
    {
        elog(__METHOD__);

        //$cmd = "ps -eo rss:10,vsz:10,%cpu:5,cmd --sort=rss | grep -v \"^\s\+0\" | cut -c -79";
        $cmd = "ps -eo rss:10,vsz:10,%cpu:5,cmd --sort=-%cpu,-rss | grep -v \"^\s\+0\"";
        $output = shell_exec($cmd);

        if ($output === null || $output === false)
        {
            Util::log('Error: Unable to fetch process information');
        }

        return $this->t->list(['procs' => $output]);
    }
}
