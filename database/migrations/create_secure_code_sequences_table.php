<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('secure-code.sequences.table', 'secure_code_sequences');

        Schema::create($table, function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('period_key', 20)->default('');
            $table->unsignedBigInteger('last_value')->default(0);
            $table->timestamps();

            $table->unique(['key', 'period_key'], 'uq_sequence_period');
        });
    }

    public function down(): void
    {
        $table = config('secure-code.sequences.table', 'secure_code_sequences');

        Schema::dropIfExists($table);
    }
};
