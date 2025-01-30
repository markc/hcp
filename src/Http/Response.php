<?php

declare(strict_types=1);

namespace HCP\Http;

class Response
{
    private string $content = '';
    private array $headers = [];
    private int $statusCode = 200;
    private string $contentType = 'text/html';

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function addHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setContentType(string $type): self
    {
        $this->contentType = $type;
        $this->addHeader('Content-Type', $type);
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }
            http_response_code($this->statusCode);
        }
        echo $this->content;
    }

    public function json(mixed $data): self
    {
        $this->setContentType('application/json');
        $this->content = json_encode($data, JSON_PRETTY_PRINT);
        return $this;
    }

    public function text(string $content): self
    {
        $this->setContentType('text/plain');
        $this->content = $content;
        return $this;
    }

    public function html(string $content): self
    {
        $this->setContentType('text/html');
        $this->content = $content;
        return $this;
    }
}
