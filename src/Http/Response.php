<?php

declare(strict_types=1);

namespace RenalTales\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Simple HTTP Response Implementation
 *
 * Basic implementation of PSR-7 ResponseInterface for the application.
 * Provides minimal functionality needed for HTTP responses.
 *
 * @package RenalTales\Http
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class Response implements ResponseInterface
{
    private int $statusCode;
    private string $reasonPhrase;
    private array $headers;
    private StreamInterface $body;
    private string $protocolVersion;

    /**
     * HTTP status code phrases
     */
    private const REASON_PHRASES = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        422 => 'Unprocessable Entity',
        500 => 'Internal Server Error',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
    ];

    /**
     * Constructor
     *
     * @param int $statusCode HTTP status code
     * @param array<string, string|array<string>> $headers HTTP headers
     * @param StreamInterface|string|null $body Response body
     * @param string $protocolVersion HTTP protocol version
     */
    public function __construct(
        int $statusCode = 200,
        array $headers = [],
        $body = null,
        string $protocolVersion = '1.1'
    ) {
        $this->statusCode = $statusCode;
        $this->reasonPhrase = self::REASON_PHRASES[$statusCode] ?? '';
        $this->headers = $this->normalizeHeaders($headers);
        $this->body = $body instanceof StreamInterface ? $body : new Stream($body ?? '');
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        $new = clone $this;
        $new->statusCode = (int) $code;
        $new->reasonPhrase = $reasonPhrase ?: (self::REASON_PHRASES[$code] ?? '');
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version): ResponseInterface
    {
        $new = clone $this;
        $new->protocolVersion = (string) $version;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name): array
    {
        $header = $this->headers[strtolower($name)] ?? [];
        return is_array($header) ? $header : [$header];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value): ResponseInterface
    {
        $new = clone $this;
        $new->headers[strtolower($name)] = is_array($value) ? $value : [$value];
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value): ResponseInterface
    {
        $new = clone $this;
        $headerName = strtolower($name);
        $existing = $new->headers[$headerName] ?? [];
        $new->headers[$headerName] = array_merge($existing, is_array($value) ? $value : [$value]);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name): ResponseInterface
    {
        $new = clone $this;
        unset($new->headers[strtolower($name)]);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body): ResponseInterface
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    /**
     * Normalize headers to lowercase keys
     *
     * @param array<string, string|array<string>> $headers
     * @return array<string, array<string>>
     */
    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $normalized[strtolower($name)] = is_array($value) ? $value : [$value];
        }
        return $normalized;
    }
}
