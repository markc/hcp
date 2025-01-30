<?php

declare(strict_types=1);

namespace HCP\Config;

class AppConfig
{
    private array $config;
    private static ?AppConfig $instance = null;

    private function __construct(array $initialConfig)
    {
        $this->config = $initialConfig;
    }

    public static function getInstance(array $initialConfig = []): self
    {
        if (self::$instance === null)
        {
            self::$instance = new self($initialConfig);
        }
        return self::$instance;
    }

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->config[$key] = $value;
    }

    public function getAll(): array
    {
        return $this->config;
    }
}
