<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('key', 128)->unique();
            $table->text('description')->nullable();
            $table->string('source', 64);
            $table->string('sensitivity', 16);
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->index(['status', 'sensitivity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
