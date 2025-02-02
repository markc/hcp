<?php

declare(strict_types=1);
// src/Plugins/Example/Model.php 20250201 - 20250201
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Example;

use HCP\Plugin;

class Model extends Plugin
{
    public function create(): string
    {
        elog(__METHOD__);

        return $this->t->create([]);
    }

    public function read(): string
    {
        elog(__METHOD__);

        return $this->t->read([]);
    }

    public function update(): string
    {
        elog(__METHOD__);

        return $this->t->update([]);
    }

    public function delete(): string
    {
        elog(__METHOD__);

        return $this->t->delete([]);
    }

    public function list(): string
    {
        elog(__METHOD__);

        return $this->t->list([]);
    }
}
