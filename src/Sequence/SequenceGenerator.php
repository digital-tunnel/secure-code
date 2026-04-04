<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Sequence;

use Illuminate\Support\Facades\DB;

final class SequenceGenerator
{
    /**
     * Atomically allocate the next $count sequence numbers.
     *
     * Uses an autonomous transaction with SELECT ... FOR UPDATE to guarantee
     * no gaps and no duplicates, even under heavy concurrent load.
     *
     * @return int[] Array of allocated sequence numbers
     */
    public function allocate(
        string $key,
        string $periodKey = '',
        int $count = 1,
        int $startAt = 1,
        ?string $connection = null,
    ): array {
        $conn = DB::connection(
            $connection ?? $this->configConnection()
        );

        $table = $this->configTable();
        $allocated = [];

        $conn->transaction(function () use ($conn, $table, $key, $periodKey, $count, $startAt, &$allocated) {
            $row = $conn->table($table)
                ->where('key', $key)
                ->where('period_key', $periodKey)
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                $firstValue = $startAt;
                $newLastValue = ($startAt - 1) + $count;

                $conn->table($table)->insert([
                    'key' => $key,
                    'period_key' => $periodKey,
                    'last_value' => $newLastValue,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $allocated = range($firstValue, $newLastValue);
            } else {
                $oldValue = (int) $row->last_value;
                $newLastValue = $oldValue + $count;

                $conn->table($table)
                    ->where('key', $key)
                    ->where('period_key', $periodKey)
                    ->update([
                        'last_value' => $newLastValue,
                        'updated_at' => now(),
                    ]);

                $allocated = range($oldValue + 1, $newLastValue);
            }
        });

        return $allocated;
    }

    /**
     * Get the current (last allocated) value without incrementing.
     */
    public function current(
        string $key,
        string $periodKey = '',
        ?string $connection = null,
    ): ?int {
        $row = DB::connection($connection ?? $this->configConnection())
            ->table($this->configTable())
            ->where('key', $key)
            ->where('period_key', $periodKey)
            ->first();

        return $row ? (int) $row->last_value : null;
    }

    private function configConnection(): ?string
    {
        if (function_exists('config')) {
            return config('secure-code.sequences.connection');
        }

        return null;
    }

    private function configTable(): string
    {
        if (function_exists('config')) {
            return config('secure-code.sequences.table', 'secure_code_sequences');
        }

        return 'secure_code_sequences';
    }
}
