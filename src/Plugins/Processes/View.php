<?php

declare(strict_types=1);
// Created: 20170225 - Updated: 20250202
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Processes;

use HCP\Theme;
use HCP\Util;

class View extends Theme
{
    public function list(array $in = []): string
    {
        Util::elog(__METHOD__);

        // Check if this is an AJAX request
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        )
        {
            return '
          <div class="col-12">
            <h5>Process List <small>(' . ($in['procs'] ? (count(explode("\n", $in['procs'])) - 1) : 0) . ')</small></h5>
            <div class="table-responsive">
              <pre><code>' . ($in['procs'] ?? 'No process data available') . '
            </code></pre>
            </div>
          </div>';
        }

        return '
          <div class="col-12 col-md-6">
            <h3><i class="bi bi-diagram-2-fill"></i> Processes</h3>
          </div>
          <div class="col-12 col-md-6">
            <form id="refreshForm" method="post" class="d-flex">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" id="o" name="o" value="Processes">
              <div class="ms-auto">
                <button type="submit" class="btn btn-primary bslink"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Refresh</button>
              </div>
            </form>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row" id="processContent">
          <div class="col-12">
            <h5>Process List <small>(' . ($in['procs'] ? (count(explode("\n", $in['procs'])) - 1) : 0) . ')</small></h5>
            <div class="table-responsive">
              <pre><code>' . ($in['procs'] ?? 'No process data available') . '
            </code></pre>
            </div>
          </div>
        </div>
        <script>
        document.getElementById("refreshForm").addEventListener("submit", function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            fetch(\'?x=main&o=Processes\', {
                method: "POST",
                headers: {
                    \'X-Requested-With\': \'XMLHttpRequest\'
                },
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                document.getElementById("processContent").innerHTML = text;
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Failed to refresh process information");
            });
        });
        </script>';
    }
}
