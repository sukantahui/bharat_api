<?php
namespace App\Exceptions;

use RuntimeException;
use PDOException;

class DatabaseConnectionException extends RuntimeException
{
    protected $code;

    public function __construct(PDOException $e)
    {
        parent::__construct("Database connection error: " . $e->getMessage(), $e->getCode(), $e);
        $this->code = $e->getCode();
    }

    public function getErrorCode(): int
    {
        return $this->code;
    }
}