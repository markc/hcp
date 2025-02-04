<?php

declare(strict_types=1);
// Created: 20150101 - Updated: 20250204
// Copyright (C) 2015-2025 Mark Constable <markc@renta.net> (AGPL-3.0)

namespace HCP;

class Plugin
{
    protected string $tbl = '';
    protected bool $isValid = false;

    public function __construct(
        protected readonly Controller $controller
    )
    {
        Util::elog(__METHOD__);

        $this->isValid = $this->validateAccess();
        if ($this->isValid)
        {
            $this->initializeDatabase();
        }
    }

    protected function validateAccess(): bool
    {
        Util::elog(__METHOD__);

        $plugin = $this->controller->input['plugin'] ?? '';
        $action = $this->controller->input['action'] ?? '';

        if ($plugin === 'Auth' && in_array($action, ['list', 'create', 'resetpw'], true))
        {
            return true;
        }

        if (!Util::is_usr())
        {
            Util::redirect($this->controller->config->self . '?plugin=Auth');
            return false;
        }

        return true;
    }

    protected function initializeDatabase(): void
    {
        Util::elog(__METHOD__);

        if ($this->tbl && is_null(Db::$dbh))
        {
            Db::$dbh = new Db($this->controller->config->db);
            Db::$tbl = $this->tbl;
        }
    }

    public function create(): array
    {
        Util::elog(__METHOD__);

        if (!$this->isValid)
        {
            return ['status' => 'error', 'message' => 'Access denied'];
        }

        if (Util::is_post())
        {
            $input = $this->controller->input;
            $input['updated'] = $input['created'] = date('Y-m-d H:i:s');

            if (Db::create($input))
            {
                Util::log('Item created successfully', 'success');
                return [
                    'status' => 'success',
                    'message' => 'Item created successfully',
                    'redirect' => true
                ];
            }
        }

        return [
            'status' => 'form',
            'message' => 'Create new item',
            'data' => $this->controller->input
        ];
    }

    public function read(): array
    {
        Util::elog(__METHOD__);

        if (!$this->isValid)
        {
            return ['status' => 'error', 'message' => 'Access denied'];
        }

        $item = Db::read('*', 'id', $this->controller->input['item'], '', 'one');

        return [
            'status' => 'success',
            'message' => 'Item details',
            'data' => $item
        ];
    }

    public function update(): array
    {
        Util::elog(__METHOD__);

        if (!$this->isValid)
        {
            return ['status' => 'error', 'message' => 'Access denied'];
        }

        if (Util::is_post())
        {
            $input = $this->controller->input;
            $input['updated'] = date('Y-m-d H:i:s');

            if (Db::update($input, [['id', '=', $input['item']]]))
            {
                Util::log('Item ' . $input['item'] . ' updated', 'success');
                return [
                    'status' => 'success',
                    'message' => 'Item updated successfully',
                    'redirect' => true
                ];
            }
        }

        return $this->read();
    }

    public function delete(): array
    {
        Util::elog(__METHOD__);

        if (!$this->isValid)
        {
            return ['status' => 'error', 'message' => 'Access denied'];
        }

        if (Util::is_post() && $this->controller->input['item'])
        {
            if (Db::delete([['id', '=', $this->controller->input['item']]]))
            {
                Util::log('Item ' . $this->controller->input['item'] . ' removed', 'success');
                return [
                    'status' => 'success',
                    'message' => 'Item deleted successfully',
                    'redirect' => true
                ];
            }
        }

        return [
            'status' => 'confirm',
            'message' => 'Confirm deletion',
            'item' => $this->controller->input['item']
        ];
    }

    public function list(): array
    {
        Util::elog(__METHOD__);

        if (!$this->isValid)
        {
            return ['status' => 'error', 'message' => 'Access denied'];
        }

        $items = Db::read('*', '', '', 'ORDER BY `updated` DESC');

        return [
            'status' => 'success',
            'message' => 'Items list',
            'data' => $items
        ];
    }
}
