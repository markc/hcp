<?php

declare(strict_types=1);
// plugins/processes.php 20170225 - 20260128
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Processes extends Plugin
{
    public function list(): string
    {
        elog(__METHOD__);

        $cmd = "ps -eo rss:10,vsz:10,%cpu:5,cmd --sort=rss | grep -v \"^\s\+0\" | cut -c -79";
        return $this->t->list(['procs' => shell_exec($cmd)]);
    }
}
