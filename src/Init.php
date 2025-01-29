<?php

declare(strict_types=1);

namespace HCP;

use HCP\Util;

class Init
{
    private $theme;

    public function __construct(object $g)
    {
        elog(__METHOD__);

        $this->initializeSession($g);
        $this->loadTheme($g);
        $this->processPlugin($g);
        $this->processOutput($g);
    }

    private function initializeSession(object $g): void
    {
        elog(__METHOD__);

        session_start();
        $g->cfg['host'] ??= getenv('HOSTNAME');
        Util::cfg($g);
        $g->in = Util::esc($g->in);
        $g->cfg['self'] = str_replace('index.php', '', $_SERVER['PHP_SELF']);

        if (!isset($_SESSION['c'])) {
            $_SESSION['c'] = Util::random_token(32);
        }

        // Session variables
        Util::ses('o');
        Util::ses('m');
        Util::ses('l');
    }

    private function loadTheme(object $g): void
    {
        elog(__METHOD__);

        $currentPlugin = $g->in['o'];
        $viewClass = "HCP\\Plugins\\{$currentPlugin}\\View";

        elog(__METHOD__ . " viewClass=$viewClass");

        // Try to load plugin-specific view
        if (class_exists($viewClass)) {
            $this->theme = new $viewClass($g);
        } else {
            // Fallback to default Theme
            $this->theme = new Theme($g);
        }
    }

    private function processPlugin(object $g): void
    {
        elog(__METHOD__);

        $pluginClass = "HCP\\Plugins\\{$g->in['o']}\\Model";

        elog(__METHOD__ . " pluginClass=$pluginClass");

        if (class_exists($pluginClass)) {
            $g->in['a'] ? Util::chkapi($g) : Util::remember($g);
            $g->out['main'] = (string) new $pluginClass($this->theme);
        } else {
            $g->out['main'] = 'Error: no plugin object!';
        }
    }

    private function processOutput(object $g): void
    {
        elog(__METHOD__);

        if (empty($g->in['x'])) {
            foreach ($g->out as $k => $v) {
                $g->out[$k] = method_exists($this->theme, $k) ? $this->theme->{$k}() : $v;
            }
        }
    }

    public function __destruct()
    {
        elog(__FILE__ . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' .
            round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']), 4) . "\n");
    }

    public function __toString(): string
    {
        elog(__METHOD__);

        $g = $this->theme->g;

        $x = $g->in['x'];

        if ('text' === $x) {
            return preg_replace('/^\h*\v+/m', '', strip_tags($g->out['main']));
        }

        if ('json' === $x) {
            header('Content-Type: application/json');
            return $g->out['main'];
        }

        if ($x) {
            $out = $g->out[$x] ?? '';
            if ($out) {
                header('Content-Type: application/json');
                return json_encode($out, JSON_PRETTY_PRINT);
            }
        }

        return $this->theme->html();
    }
}
