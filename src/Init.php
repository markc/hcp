<?php

declare(strict_types=1);

namespace HCP;

use HCP\Config\AppConfig;
use HCP\Http\Request;
use HCP\Http\Response;
use HCP\Plugin\PluginManager;
use HCP\Util;

class Init
{
    private object $theme;
    private Request $request;
    private Response $response;
    private AppConfig $config;
    private PluginManager $pluginManager;

    public function __construct(object $g)
    {
        elog(__METHOD__);

        $this->initializeCore($g);
        $this->processRequest($g);
        $this->processOutput($g);
    }

    private function initializeCore(object $g): void
    {
        $this->config = AppConfig::getInstance($g->cfg);
        $this->request = new Request();
        $this->response = new Response();
        $this->pluginManager = new PluginManager();

        $this->initializeSession($g);
        Theme::setGlobal($g);

        if (!empty($g->in['t'])) {
            Theme::setTheme($g->in['t']);
        }
        $this->loadTheme($g);
    }

    private function initializeSession(object $g): void
    {
        elog(__METHOD__);

        session_start();

        $g->cfg['host'] ??= getenv('HOSTNAME');
        Util::cfg($g);
        $g->in = Util::esc($g->in);
        $g->cfg['self'] = str_replace('index.php', '', $this->request->getServerParam('PHP_SELF'));

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
            // Fallback to TopNav theme
            $this->theme = Theme::getTheme();
        }

        // Assign theme instance to g->t for access in plugins
        $g->t = $this->theme;
    }

    private function processRequest(object $g): void
    {
        elog(__METHOD__);

        $pluginName = $g->in['o'];
        $plugin = $this->pluginManager->loadPlugin($pluginName, $this->theme);

        if ($plugin) {
            $g->in['a'] ? Util::chkapi($g) : Util::remember($g);
            $g->out['main'] = (string) $plugin;
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

        $x = $g->in['x'];
        $content = '';

        if ('text' === $x) {
            $content = preg_replace('/^\h*\v+/m', '', strip_tags($g->out['main']));
            $this->response->text($content);
        } elseif ('json' === $x) {
            $this->response->json($g->out['main']);
        } elseif ($x) {
            if ($x === 'html') {
                $this->response->html($g->out['main']);
            } else {
                $out = $g->out[$x] ?? '';
                if ($out) {
                    $this->response->json($out);
                }
            }
        } else {
            $this->response->html($this->theme->html());
        }
    }

    public function __destruct()
    {
        elog(__FILE__ . ' ' . $this->request->getServerParam('REMOTE_ADDR') . ' ' .
            round((microtime(true) - $this->request->getServerParam('REQUEST_TIME_FLOAT')), 4) . "\n");
    }

    public function __toString(): string
    {
        elog(__METHOD__);
        return $this->response->getContent();
    }
}
