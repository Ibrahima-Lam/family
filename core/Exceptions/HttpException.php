<?php

namespace Core\Exceptions;

use RuntimeException;

final class HttpException extends RuntimeException
{
    public function __construct(
        private readonly int $statusCode,
        string $message = ''
    ) {
        parent::__construct($message === '' ? 'Erreur HTTP' : $message, $statusCode);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
