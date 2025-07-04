<?php
declare(strict_types=1);

namespace RenalTales\Service;

use RenalTales\Core\Config;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

abstract class Service
{
    protected Config $config;
    protected LoggerInterface $logger;

    public function __construct(Config $config = null)
    {
        $this->config = $config ?? new Config(dirname(__DIR__, 2) . '/config/config.php');
        $this->initializeLogger();
    }

    protected function initializeLogger(): void
    {
        $logger = new Logger(static::class);
        $logger->pushHandler(new StreamHandler(
            dirname(__DIR__, 2) . '/var/logs/app.log',
            Logger::DEBUG
        ));
        $this->logger = $logger;
    }

    protected function logInfo(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    protected function logError(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    protected function logWarning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    protected function validateRequired(array $data, array $fields): void
    {
        $missing = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new \InvalidArgumentException(
                'Missing required fields: ' . implode(', ', $missing)
            );
        }
    }

    protected function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address');
        }
    }

    protected function validateLength(string $value, string $field, int $min, int $max): void
    {
        $length = mb_strlen($value);
        if ($length < $min || $length > $max) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s must be between %d and %d characters',
                    $field,
                    $min,
                    $max
                )
            );
        }
    }

    protected function validateEnum(string $value, string $field, array $allowed): void
    {
        if (!in_array($value, $allowed)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s must be one of: %s',
                    $field,
                    implode(', ', $allowed)
                )
            );
        }
    }

    protected function validateDate(string $date, string $format = 'Y-m-d'): void
    {
        $d = \DateTime::createFromFormat($format, $date);
        if (!$d || $d->format($format) !== $date) {
            throw new \InvalidArgumentException(
                sprintf('Invalid date format. Expected %s', $format)
            );
        }
    }

    protected function sanitizeString(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    protected function sanitizeHTML(string $value, array $allowedTags = []): string
    {
        if (empty($allowedTags)) {
            $allowedTags = [
                'p', 'br', 'b', 'strong', 'i', 'em', 'ul', 'ol', 'li',
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote'
            ];
        }

        return strip_tags($value, $allowedTags);
    }

    protected function generateSlug(string $text): string
    {
        // Transliterate non-ASCII characters
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
        
        // Convert to lowercase
        $text = strtolower($text);
        
        // Replace non-alphanumeric characters with hyphens
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        
        // Remove multiple consecutive hyphens
        $text = preg_replace('/-+/', '-', $text);
        
        // Remove leading and trailing hyphens
        return trim($text, '-');
    }
}
