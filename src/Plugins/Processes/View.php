<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/processes.php 20170225 - 20250128
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Processes;

use HCP\TopNav;

class View extends TopNav
{
    public function list(array $in): string
    {
        elog(__METHOD__);

        return '
          <div class="col-12 col-sm-6">
            <h3><i class="fas fa-code-branch fa-fw"></i> Processes</h3>
          </div>
          <div class="col-12 col-sm-6">
            <form method="post" class="form-inline">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" id="o" name="o" value="processes">
              <div class="form-group ml-auto">
                <button type="submit" class="btn btn-primary"><i class="fas fa-sync-alt fa-fw" aria-hidden="true"></i> Refresh</button>
              </div>
            </form>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <h5>Process List <small>(' . ($in['procs'] ? (count(explode("\n", $in['procs'])) - 1) : 0) . ')</small></h5>
            <pre><code>' . ($in['procs'] ?? 'No process data available') . '
            </code></pre>
          </div>
        </div>';
    }
}
