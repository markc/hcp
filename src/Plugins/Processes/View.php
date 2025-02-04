<?php

declare(strict_types=1);
// Created: 20170225 - Updated: 20250204
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Processes;

use HCP\Util;

class View
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
                    <h5>Process List <small>(' . ($in['message'] ? (count(explode("\n", $in['message'])) - 1) : 0) . ')</small></h5>
                    <div class="table-responsive">
                        <pre><code>' . ($in['message'] ?? 'No process data available') . '
                        </code></pre>
                    </div>
                </div>';
        }

        return '
                <div class="d-flex flex-nowrap align-items-center mb-3">
                    <h2 class="mb-0 text-nowrap flex-grow-0"><i class="bi bi-diagram-2-fill"></i> Processes</h2>
                    <form id="refreshForm" method="post" class="flex-grow-1 d-flex justify-content-end">
                        <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
                        <input type="hidden" name="plugin" value="Processes">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Refresh</button>
                    </form>
                </div>
                <div class="row" id="processContent">
                    <div class="col-12">
                        <h5>Process List <small>(' . ($in['message'] ? (count(explode("\n", $in['message'])) - 1) : 0) . ')</small></h5>
                        <div class="table-responsive">
                            <pre><code>' . ($in['message'] ?? 'No process data available') . '
                            </code></pre>
                        </div>
                    </div>
                </div>
                <script>
                    document.getElementById(\'refreshForm\').addEventListener(\'submit\', function(e) {
                        e.preventDefault();
                        
                        const formData = new FormData(this);
                        const button = this.querySelector(\'button\');
                        const icon = button.querySelector(\'i\');
                        
                        // Disable button and show loading state
                        button.disabled = true;
                        icon.classList.add(\'spin\');
                        
                        fetch(\'?xhr=main&plugin=Processes\', {
                            method: \'POST\',
                            body: formData,
                            headers: {
                                \'X-Requested-With\': \'XMLHttpRequest\'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(\'HTTP error! status: \' + response.status);
                            }
                            return response.text();
                        })
                        .then(html => {
                            try {
                                const tempDiv = document.createElement(\'div\');
                                tempDiv.innerHTML = html;
                                
                                // Find the process content in the new content
                                const newContent = tempDiv.querySelector(\'.col-12\');
                                if (!newContent) {
                                    throw new Error(\'Could not find updated content in response\');
                                }
                                
                                // Find the existing content to replace
                                const existingContent = document.querySelector(\'#processContent\');
                                if (!existingContent) {
                                    throw new Error(\'Could not find existing content to update\');
                                }
                                
                                // Replace the content
                                existingContent.innerHTML = newContent.outerHTML;
                            } catch (error) {
                                console.error(\'Error updating content:\', error);
                                console.error(\'Response HTML:\', html);
                                throw error;
                            }
                        })
                        .catch(error => {
                            console.error(\'Error:\', error);
                            alert(\'Failed to update process information: \' + error.message);
                        })
                        .finally(() => {
                            // Re-enable button and remove loading state
                            button.disabled = false;
                            icon.classList.remove(\'spin\');
                        });
                    });
                    
                    // Add spin animation style
                    const style = document.createElement(\'style\');
                    style.textContent = \'@keyframes spin {\' +
                        \'from { transform: rotate(0deg); }\' +
                        \'to { transform: rotate(360deg); }\' +
                        \'}\' +
                        \'.spin {\' +
                        \'animation: spin 1s linear infinite;\' +
                        \'}\';
                    document.head.appendChild(style);
                </script>';
    }
}
