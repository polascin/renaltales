<?php
declare(strict_types=1);

namespace RenalTales\Core\Exceptions;

class NotFoundException extends HttpException
{
    public function __construct(string $message = 'Not Found', ?\Throwable $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}
