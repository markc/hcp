<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/infosys.php 20170225 - 20230604
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\InfoSys;

use HCP\Theme;

class View extends Theme
{
    public function list(array $in = []): string
    {
        elog(__METHOD__);

        extract($in);

        return <<<HTML
          <div class="col-12 col-md-6">
            <h3><i class="bi bi-hdd-rack-fill"></i> System Info</h3>
          </div>
          <div class="col-12 col-md-6">
            <form id="refreshForm" method="post" class="d-flex">
              <input type="hidden" name="c" value="{$_SESSION['c']}">
              <input type="hidden" name="o" value="InfoSys">
              <div class="ms-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Refresh</button>
              </div>
            </form>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12 col-md-6">
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <tbody>
                  <tr>
                    <td class="w-25"><b>Hostname</b></td>
                    <td class="w-75">{$hostname}</td>
                  </tr>
                  <tr>
                    <td class="w-25"><b>Host IP</b></td>
                    <td class="w-75">{$host_ip}</td>
                  </tr>
                  <tr>
                    <td class="w-25"><b>Distro</b></td>
                    <td class="w-75">{$os_name}</td>
                  </tr>
                  <tr>
                    <td class="w-25"><b>Uptime</b></td>
                    <td class="w-75">{$uptime}</td>
                  </tr>
                  <tr>
                    <td class="w-25"><b>CPU Load</b></td>
                    <td class="w-75">{$loadav} - {$cpu_num} cpus</td>
                  </tr>
                  <tr>
                    <td class="w-25"><b>CPU Model</b></td>
                    <td class="w-75">{$cpu_name}</td>
                  </tr>
                  <tr>
                    <td class="w-25"><b>Kernel Version</b></td>
                    <td class="w-75">{$kernel}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <br>
            <h5>RAM <small> - Used: {$mem_used} - Total: {$mem_total} - Free: {$mem_free}</small></h5>
            <div class="progress">
              <div class="progress-bar bg-{$mem_color}" role="progressbar" aria-valuenow="{$mem_pcnt}"
              aria-valuemin="0" aria-valuemax="100" style="width:{$mem_pcnt}%" title="Used Memory">{$mem_text}</div>
            </div>
            <br>
            <h5>Disk <small> - Used: {$dsk_used} - Total: {$dsk_total} - Free: {$dsk_free}</small></h5>
            <div class="progress">
              <div class="progress-bar bg-{$dsk_color}" role="progressbar" aria-valuenow="{$dsk_pcnt}"
              aria-valuemin="0" aria-valuemax="100" style="width:{$dsk_pcnt}%" title="Used Disk Space">{$dsk_text}</div>
            </div>
            <br>
            <h5>CPU <small> - {$cpu_all}</small></h5>
            <div class="progress">
              <div class="progress-bar bg-{$cpu_color}" role="progressbar" aria-valuenow="{$cpu_pcnt}"
              aria-valuemin="0" aria-valuemax="100" style="width:{$cpu_pcnt}%" title="Used Disk Space">{$cpu_text}</div>
            </div>
            <br>
          </div>
        </div>
        <script>
        document.getElementById("refreshForm").addEventListener("submit", function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const button = this.querySelector('button');
            const icon = button.querySelector('i');
            
            // Disable button and show loading state
            button.disabled = true;
            icon.classList.add('spin');
            
            fetch('?x=main&o=InfoSys', {
                method: "POST",
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.text();
            })
            .then(html => {
                try {
                    // Find the container that holds the system info content
                    const container = document.querySelector('#refreshForm').closest('.row').parentElement;
                    if (!container) {
                        throw new Error('Could not find system info container');
                    }
                    
                    // Update the container content
                    container.innerHTML = html;
                } catch (error) {
                    console.error("Error updating content:", error);
                    console.error("Response HTML:", html);
                    throw error;
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Failed to update system information: " + error.message);
            })
            .finally(() => {
                // Re-enable button and remove loading state
                button.disabled = false;
                icon.classList.remove('spin');
            });
        });
        
        // Add spin animation style
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            .spin {
                animation: spin 1s linear infinite;
            }
        `;
        document.head.appendChild(style);
        </script>
HTML;
    }
}
