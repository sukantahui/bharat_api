<?php

namespace App\Exceptions;

use RuntimeException;
use Illuminate\Database\QueryException;

class DatabaseOperationException extends RuntimeException
{
    protected $query;
    protected $bindings;
    protected $errorInfo;

    public function __construct(QueryException $originalException)
    {
        $message = "Database operation failed: " . $originalException->getMessage();

        // Force cast code to int, defaulting to 0 if not numeric
        $code = is_numeric($originalException->getCode())
            ? (int) $originalException->getCode()
            : 0;

        parent::__construct($message, $code, $originalException);

        $this->query = $originalException->getSql();
        $this->bindings = $originalException->getBindings();
        $this->errorInfo = $originalException->errorInfo ?? null;
    }


    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function getErrorInfo(): ?array
    {
        return $this->errorInfo;
    }

    public function context(): array
    {
        return [
            'query' => $this->query,
            'bindings' => $this->bindings,
            'error_info' => $this->errorInfo,
            'error_code' => $this->getCode()
        ];
    }
}