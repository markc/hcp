<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/mailgraph.php 20170225 - 20230625
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_MailGraph extends Themes_Bootstrap_Theme
{
    public function list(array $in): string
    {
        return '
        <h3><i class="bi bi-bar-chart"></i> MailServer Graph</h3>
        <div class="row">
          <div class="text-center">' . $in['mailgraph'] . '
          </div>
        </div>';
    }
}
