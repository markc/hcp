<?php

declare(strict_types=1);

namespace HCP\Http;

class Request
{
    private array $get;
    private array $post;
    private array $server;
    private array $session;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->session = $_SESSION ?? [];
    }

    public function getParam(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    public function getPostParam(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    public function isPost(): bool
    {
        return $this->server['REQUEST_METHOD'] === 'POST';
    }

    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'];
    }

    public function getServerParam(string $key, $default = null)
    {
        return $this->server[$key] ?? $default;
    }

    public function getSessionParam(string $key, $default = null)
    {
        return $this->session[$key] ?? $default;
    }

    public function getAllParams(): array
    {
        return [
            'get' => $this->get,
            'post' => $this->post,
            'server' => $this->server,
            'session' => $this->session
        ];
    }
}
