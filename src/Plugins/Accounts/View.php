<?php

declare(strict_types=1);
// Created: 20170225 - Updated: 20250202
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP\Plugins\Accounts;

use HCP\Db;
use HCP\Util;

class View
{
    public function create(array $in): string
    {
        Util::elog(__METHOD__);

        return $this->modal([
            'id'        => 'createmodal',
            'title'     => 'Create new user',
            'action'    => 'create',
            'body'      => $this->modal_body($in),
            'footer'    => '
            <button type="submit" class="btn btn-primary">Create</button>'
        ]);
    }

    public function read(array $in): string
    {
        Util::elog(__METHOD__);

        return $this->modal([
            'id'        => 'readmodal',
            'title'     => 'Update user',
            'action'    => 'update',
            'body'      => $this->modal_body($in),
            'footer'    => '
                  <button type="submit" class="btn btn-primary">Update</button>
                  <a href="?plugin=Accounts&action=delete&i=' . $this->g->input['i'] . '&x=modal" class="btn btn-danger bslink" data-bs-dismiss="modal">Delete</a>'
        ]);
    }

    public function delete(): ?string
    {
        Util::elog(__METHOD__);

        $usr = Db::read('login', 'id', $this->g->input['i'], '', 'one');

        return $this->modal([
            'id'        => 'deletemodal',
            'title'     => 'Remove User',
            'action'    => 'delete',
            'body'      => sprintf('<p class="text-center">Are you sure you want to remove this user?<br><b>%s</b></p>', $usr['login']),
            'footer'    => '<button type="submit" class="btn btn-danger">Remove</button>',
            'hidden'    => sprintf('<input type="hidden" name="i" value="%s">', $this->g->input['i'])
        ]);
    }

    public function list(array $in = []): string
    {
        Util::elog(__METHOD__);

        return <<<HTML
        <div class="row">
            <h1>
                <i class="bi bi-people-fill"></i> Accounts
                <a href="?plugin=Accounts&action=create&x=modal" class="bslink" title="Add new account">
                    <small><i class="bi bi-plus-circle"></i></small>
                </a>
            </h1>
        </div>
        <div class="table-responsive">
            <table id="accounts" class="table table-borderless table-striped w-100">
                <thead>
                    <tr>
                        <th>Login</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Alt Email</th>
                        <th>Access Level</th>
                        <th>Group</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div id="modals"></div>
        <script>
        $(document).ready(function() {
            var table = $("#accounts").DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": "?plugin=Accounts&action=list&x=json&f=json",
                "scrollX": true,
                "info": true,
                "columnDefs": [
                    {"targets":0, "className":"text-truncate"},
                    {"targets":3, "className":"text-truncate"},
                ]
            });

            $(document).on("click", ".bslink", function(event){
                event.preventDefault();
                var url = $(this).attr("href");
                var m = new URLSearchParams(url).get("m");
                $("#modals").empty().load(url, function() {
                    $("#" + m + "modal").modal("show").on('hidden.bs.modal', function () {
                        $("#modals").empty();
                });
              
                // Handle form submission for delete
                if (m === "delete") {
                    $("#deletemodal form").on("submit", function(e) {
                        e.preventDefault();
                        $.post($(this).attr("action"), $(this).serialize())
                            .done(function() {
                                $("#deletemodal").modal("hide");
                                table.ajax.reload();
                            });
                        });
                    }
                });
            });
        });
        </script>
        HTML;
    }

    private function modal_body(array $in): string
    {
        Util::elog(__METHOD__);

        $acl = $_SESSION['usr']['acl'];
        $grp = $_SESSION['usr']['grp'];

        $acl_levels = [
            0 => 'Super Admin',
            1 => 'Admin',
            2 => 'User',
            3 => 'Guest'
        ];
        $acl_ary = array_map(fn($k, $v) => [$v, $k], array_keys($acl_levels), $acl_levels);
        $acl_buf = $this->dropdown($acl_ary, 'acl', "{$acl}", '', 'form-select');

        $res = Db::qry('SELECT login, id FROM `accounts` WHERE acl IN (0, 1)');

        $grp_ary = array_map(fn($row) => [$row['login'], $row['id']], $res);
        $grp_buf = $this->dropdown($grp_ary, 'grp', "{$grp}", '', 'form-select');

        $aclgrp_buf = <<<HTML
        <div class="row">
          <div class="col-6 mb-3">
            <label for="acl" class="form-label">ACL</label>$acl_buf
          </div>
          <div class="col-6 mb-3">
            <label for="grp" class="form-label">Group</label>$grp_buf
          </div>
        </div>
        HTML;

        return <<<HTML
        <div class="row">
          <div class="col-6 mb-3">
            <label for="login" class="form-label">Email ID</label>
            <input type="email" class="form-control" id="login" name="login" value="{$in['login']}" required>
          </div>
          <div class="col-6 mb-3">
            <label for="altemail" class="form-label">Alt Email</label>
            <input type="text" class="form-control" id="altemail" name="altemail" value="{$in['altemail']}">
          </div>
        </div>
        <div class="row">
          <div class="col-6 mb-3">
            <label for="fname" class="form-label">First Name</label>
            <input type="text" class="form-control" id="fname" name="fname" value="{$in['fname']}" required>
          </div>
          <div class="col-6 mb-3">
            <label for="lname" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="lname" name="lname" value="{$in['lname']}" required>
          </div>
        </div>
        $aclgrp_buf
        HTML;
    }
}
