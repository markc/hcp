<?php

declare(strict_types=1);

namespace HCP;

use HCP\Db;
use HCP\Util;
use HCP\Init;

class Plugin
{
    protected string $buf = '';
    protected mixed $dbh = null;
    protected string $tbl = '';
    protected Init $init;
    protected Theme $theme;

    public function __construct(Theme $theme, Init $init)
    {
        Util::elog(__METHOD__);

        $this->theme = $theme;
        $this->init = $init;

        if (!$this->validateAccess())
        {
            return;
        }

        $this->initializeDatabase();
        $this->processAction();
    }

    protected function initializeDatabase(): void
    {
        Util::elog(__METHOD__);

        if ($this->tbl)
        {
            if (!is_null($this->dbh))
            {
                Db::$dbh = $this->dbh;
            }
            elseif (is_null(Db::$dbh))
            {
                Db::$dbh = new Db($this->theme->db);
            }
            Db::$tbl = $this->tbl;
        }
    }

    protected function processAction(): void
    {
        Util::elog(__METHOD__);

        $action = $this->init->input['action'] ?? 'list';

        $this->buf .= $this->{$action}();
    }

    public function __toString(): string
    {
        Util::elog(__METHOD__);

        return $this->buf;
    }

    protected function create(): string
    {
        Util::elog(__METHOD__);

        if (Util::is_post())
        {
            $this->init->input['updated'] = date('Y-m-d H:i:s');
            $this->init->input['created'] = date('Y-m-d H:i:s');
            $lid = Db::create($this->init->input);
            Util::log('Item number ' . $lid . ' created', 'success');
            Util::relist();
        }
        return $this->theme->create($this->init->input);
    }

    protected function read(): string
    {
        Util::elog(__METHOD__);
        return $this->theme->read(Db::read('*', 'id', $this->init->input['item'], '', 'one'));
    }

    protected function update(): string
    {
        Util::elog(__METHOD__);

        if (Util::is_post())
        {
            $this->init->input['updated'] = date('Y-m-d H:i:s');
            if (Db::update($this->init->input, [['id', '=', $this->init->input['item']]]))
            {
                Util::log('Item number ' . $this->init->input['item'] . ' updated', 'success');
                Util::relist();
            }
            else
            {
                Util::log('Error updating item.');
            }
        }
        return $this->read();
    }

    protected function delete(): string
    {
        Util::elog(__METHOD__);

        if (Util::is_post())
        {
            if ($this->init->input['item'])
            {
                Db::delete([['id', '=', $this->init->input['item']]]);
                Util::log('Item number ' . $this->init->input['item'] . ' removed', 'success');
                Util::relist();
            }
            else
            {
                Util::log('Error deleting item');
            }
        }
        return '';
    }

    protected function list(): string
    {
        Util::elog(__METHOD__);
        return $this->theme->list(Db::read('*', '', '', 'ORDER BY `updated` DESC'));
    }

    protected function validateAccess(): bool
    {
        $plugin = $this->init->input['plugin'] ?? '';
        $action = $this->init->input['action'] ?? '';

        // Always allow access to Auth plugin's public actions
        if ($plugin === 'Auth' && in_array($action, ['list', 'create', 'resetpw'], true))
        {
            return true;
        }

        // Require user to be logged in for all other actions
        if (!Util::is_usr())
        {
            Util::redirect($this->init->config->self . '?plugin=Auth');
            return false;
        }

        return true;
    }
}
