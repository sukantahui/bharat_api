<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

trait HandlesTransactions {

    public function executeInTransaction(callable $callback, array $context = [])
    {
        return DB::transaction(function () use ($callback, $context) {

            try {
                $result = $callback();

                Log::info('Transaction committed successfully', [
                    'context' => $context
                ]);

                return $result;

            } catch (Exception $e) {

                Log::error('Transaction failed', [
                    'error' => $e->getMessage(),
                    'context' => $context
                ]);

                throw $e; // Laravel automatically rollbacks
            }
        });
    }
}
