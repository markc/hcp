<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/domains.php 20170225 - 20230625
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Domains extends Themes_Bootstrap_Theme
{
    public function create(array $in): string
    {
        elog(__METHOD__);

        return $this->modal_content([
            'title'     => 'Create DNS Zone',
            'action'    => 'create',
            'lhs_cmd'   => '',
            'rhs_cmd'   => 'Create',
            'body'      => $this->modal_body($in)
        ]);
    }

    public function update(array $in): string
    {
        return $this->editor($in);
    }

    public function list(array $in): string
    {
        elog(__METHOD__);

        elog(var_export($in, true));

        return '
        <div class="row">
          <h3>
            <i class="bi bi-globe"></i> Domains
            <a href="?o=domains&m=create" class="bslink" title="Add new domain">
              <small><i class="bi bi-plus-circle"></i></small>
            </a>
          </h3>
        </div>
        <div class="table-responsive">
            <table id="domains" class="table table-borderless table-striped w-100">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Type</th>
                  <th>Records</th>
                  <th>Serial</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
        </div>
        <div class="modal fade" id="createmodal" tabindex="-1" role="dialog" aria-labelledby="createmodal" aria-hidden="true">
          <div class="modal-dialog" id="createdialog">
          </div>
        </div>
        <div class="modal fade" id="shwhomodal" tabindex="-1" role="dialog" aria-labelledby="readmodal" aria-hidden="true">
          <div class="modal-dialog" id="shwhodialog">
          </div>
        </div>
        <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="deletemodal" aria-hidden="true">
          <div class="modal-dialog" id="deletedialog">
          </div>
        </div>
        <script>
$(document).ready(function() {
  $("#domains").DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": { "url": "?x=json&o=domains&m=list", "deferLoading": 10 },
    "order": [[ 5, "desc" ]],
    "scrollX": true,
    "columnDefs": [
      {"targets":0, "className":"text-truncate", "width":"40%"},
      {"targets":4, "width":"3rem", "className":"text-right", "sortable": false},
      {"targets":5, "visible":false},
    ],
  });

  $(document).on("click", ".bslink", function(){
    event.preventDefault();
    var url = $(this).attr("href") + "&x=html";
    var m = new URLSearchParams(url).get("m");
    $("#" + m + "dialog").load(url, function() {
      $("#" + m + "modal", document).modal("show");
    });
  });

  $(document).on("click", ".serial", {}, (function() {
    var a = $(this);
    $.post("?x=text&increment=1&" + this.toString().split("?")[1], function(data) {
      $(a).text(data);
    });
    return false;
  }));

});
        </script>';
    }

    /*
  $("#domains").show();

  $(document).on("click", ".serial", {}, (function() {
    var a = $(this);
    $.post("?x=text&increment=1&" + this.toString().split("?")[1], function(data) {
      $(a).text(data);
    });
    return false;
  }));

  $(document).on("click", ".delete", {}, function() {
    $("#removemodalid").val($(this).attr("data-rowid"));
    $("#removemodalname").text($(this).attr("data-rowname"));
  });

  $(document).on("click", ".shwho", {}, function() {
    var $this = $(this);
    $("#shwho-name").text($this.attr("data-rowname"));
    $.post("?x=text&o=domains&m=shwho&name=" + $this.attr("data-rowname"), function(data) {
      $("#shwho-info").text(data);
    });
    return false;
  });


        $create = $this->modal([
            'id' => 'createmodal',
            'title' => 'Create DNS Zone',
            'action' => 'create',
            'footer' => 'Create',
            'body' => '
            <div class="form-group row">
              <label for="domain" class="col-sm-2 col-form-label">Domain</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="domain" name="domain">
              </div>
            </div>
            <div class="form-group row">
              <label for="ip" class="col-sm-2 col-form-label">IP</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="ip" name="ip">
              </div>
            </div>
            <div class="form-group row">
              <label for="ns1" class="col-sm-2 col-form-label">NS1</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="ns1" name="ns1">
              </div>
            </div>
            <div class="form-group row">
              <label for="ns2" class="col-sm-2 col-form-label">NS2</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="ns2" name="ns2">
              </div>
            </div>
            <div class="form-group row">
              <label for="mxhost" class="col-sm-2 col-form-label">MXHost</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="mxhost" name="mxhost">
              </div>
            </div>
            <div class="form-group row">
              <label for="spfip" class="col-sm-2 col-form-label">SPF IP</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="spfip" name="spfip">
              </div>
            </div>',
        ]);

        $remove = $this->modal([
            'id' => 'removemodal',
            'title' => 'Remove DNS Zone',
            'action' => 'delete',
            'footer' => 'Remove',
            'hidden' => '
                <input type="hidden" id="removemodalid" name="i" value="">',
            'body' => '
                <p class="text-center">Are you sure you want to remove this domain?<br><b id="removemodalname"></b></p>',
        ]);

        $shwho = $this->modal([
            'id' => 'shwhomodal',
            'title' => 'Domain Info for <b id="shwho-name"></b>',
            'action' => 'shwho',
            'footer' => '',
            'body' => '
            <pre id="shwho-info"></pre>',
        ]);



*/
    private function editor(array $in): string
    {
        $domain = $in['name'];
        $soa = isset($in['soa'])
            ? explode(' ', $in['soa'])
            : ['', '', '', 7200, 540, 604800, 300];

        if ('create' === $this->g->in['m']) {
            $serial = $hidden = '';
            $header = 'Add Domain';
            $submit = '
                <a class="btn btn-secondary" href="?o=domains&m=list">&laquo; Back</a>
                <button type="submit" id="m" name="m" value="create" class="btn btn-primary">Add Domain</button>';
        } else {
            $serial = '&nbsp;&nbsp;<small>Serial: ' . $soa[2] . '</small>';
            $header = $domain;
            $submit = '
                <a class="btn btn-secondary" href="?o=domains&m=list">&laquo; Back</a>
                <button type="submit" id="m" name="m" value="update" class="btn btn-primary">Update</button>';
            $hidden = '
            <input type="hidden" name="serial" value="' . $soa[2] . '">';
        }

        return '
          <div class="col-12">
          <h3>
            <i class="fa fa-globe fa-fw"></i>  ' . $header . $serial . '
            <a href="" title="Add new domain" data-toggle="modal" data-target="#createmodal">
              <small><i class="fas fa-plus-circle fa-fw"></i></small></a>
          </h3>
          </div>
        </div><!-- END UPPER ROW -->
        <div class="row">
          <div class="col-12">
            <form method="post" action="' . $this->g->cfg['self'] . '">
              <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
              <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
              <input type="hidden" name="i" value="' . $this->g->in['i'] . '">' . $hidden . '
              <div class="row">
                <div class="col-3">
                  <div class="form-group">
                    <label for="primary">Primary</label>
                    <input type="text" class="form-control" id="primary" name="primary" value="' . $soa[0] . '" required>
                  </div>
                </div>
                <div class="col-3">
                  <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" class="form-control" id="email" name="email" value="' . $soa[1] . '" required>
                  </div>
                </div>
                <div class="col-1">
                  <div class="form-group">
                    <label for="refresh">Refresh</label>
                    <input type="text" class="form-control" id="refresh" name="refresh" value="' . $soa[3] . '" required>
                  </div>
                </div>
                <div class="col-1">
                  <div class="form-group">
                    <label for="retry">Retry</label>
                    <input type="text" class="form-control" id="retry" name="retry" value="' . $soa[4] . '" required>
                  </div>
                </div>
                <div class="col-2">
                  <div class="form-group">
                    <label for="expire">Expire</label>
                    <input type="text" class="form-control" id="expire" name="expire" value="' . $soa[5] . '" required>
                  </div>
                </div>
                <div class="col-2">
                  <div class="form-group">
                    <label for="ttl">TTL</label>
                    <input type="text" class="form-control" id="ttl" name="ttl" value="' . $soa[6] . '" required>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-12 text-right">
                  <div class="btn-group">' . $submit . '
                  </div>
                </div>
              </div>
            </form>
          </div>';
    }

    public function delete(): ?string
    {
        $tmp = db::read('name', 'id', $this->g->in['i'], '', 'one');

        return $this->modal_content([
            'title'     => 'Remove Domain',
            'action'    => 'delete',
            'lhs_cmd'   => '',
            'rhs_cmd'   => 'Remove',
            'hidden'    => '
            <input type="hidden" name="i" value="' . $this->g->in['i'] . '">',
            'body'      => '
            <p class="text-center">Are you sure you want to remove this domain?<br><b>' . $tmp['name'] . '</b></p>',
        ]);
    }

    public function shwho(string $name, string $body): string
    {
        return $this->modal_content([
            'title'     => 'Whois summary: <b>' . $name . '</b>',
            'action'    => 'shwho',
            'lhs_cmd'   => '',
            'rhs_cmd'   => '',
            'body'      => '
            <pre>' . $body . '</pre>',
        ]);
    }

    private function modal_body(array $in): string
    {
        return '
        <div class="row mb-3">
          <div class="col-6">
            <label for="domain" class="form-label">Domain</label>
            <input type="text" class="form-control" id="domain" name="domain" value="' . $in['domain'] . '" required>
          </div>
          <div class="col-6">
            <label for="ip" class="form-label">IP</label>
            <input type="text" class="form-control" id="ip" name="ip" value="' . $in['ip'] . '" required>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-6">
            <label for="ns1" class="form-label">NS1</label>
            <input type="text" class="form-control" id="ns1" name="ns1" value="' . $in['ns1'] . '" required>
          </div>
          <div class="col-6">
            <label for="ns2" class="form-label">NS2</label>
            <input type="text" class="form-control" id="ns2" name="ns2" value="' . $in['ns2'] . '" required>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-6">
            <label for="mxhost" class="form-label">MXHost</label>
            <input type="text" class="form-control" id="mxhost" name="mxhost" value="' . $in['mxhost'] . '" required>
          </div>
          <div class="col-6">
            <label for="spfip" class="form-label">SPF IP</label>
            <input type="text" class="form-control" id="spfip" name="spfip" value="' . $in['spfip'] . '" required>
          </div>
        </div>';
    }
}
