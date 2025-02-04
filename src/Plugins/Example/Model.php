<?php

declare(strict_types=1);
// Created: 20250201 - Updated: 20250202
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Example;

use HCP\Plugin;
use HCP\Util;

class Model extends Plugin
{
    public function create(): string
    {
        Util::elog(__METHOD__);

        return $this->t->create([]);
    }

    public function read(): string
    {
        Util::elog(__METHOD__);

        return $this->t->read([]);
    }

    public function update(): string
    {
        Util::elog(__METHOD__);

        return $this->t->update([]);
    }

    public function delete(): string
    {
        Util::elog(__METHOD__);

        return $this->t->delete([]);
    }

    public function list(): string
    {
        Util::elog(__METHOD__);

        return $this->t->list([]);
    }
}
