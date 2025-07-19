<?php

declare(strict_types=1);

namespace RenalTales\Http;

use Psr\Http\Message\StreamInterface;

/**
 * Simple Stream Implementation
 *
 * Basic implementation of PSR-7 StreamInterface for response bodies.
 * Provides minimal functionality needed for HTTP response streams.
 *
 * @package RenalTales\Http
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class Stream implements StreamInterface
{
    private string $content;
    private int $position;

    /**
     * Constructor
     *
     * @param string $content Stream content
     */
    public function __construct(string $content = '')
    {
        $this->content = $content;
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        // No-op for string-based stream
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return strlen($this->content);
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return $this->position >= strlen($this->content);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        switch ($whence) {
            case SEEK_SET:
                $this->position = $offset;
                break;
            case SEEK_CUR:
                $this->position += $offset;
                break;
            case SEEK_END:
                $this->position = strlen($this->content) + $offset;
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        $this->content .= $string;
        return strlen($string);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        $result = substr($this->content, $this->position, $length);
        $this->position += strlen($result);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        return substr($this->content, $this->position);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        return $key === null ? [] : null;
    }
}
