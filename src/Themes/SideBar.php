<?php

declare(strict_types=1);

namespace HCP\Themes;

class SideBar extends Basic
{
    public function __construct(object $g)
    {
        elog(__METHOD__);

        parent::__construct($g);
    }

    public function list(array $in = []): string
    {
        elog(__METHOD__);

        return "TODO: The SideBar theme has yet to be completed";
    }
}
