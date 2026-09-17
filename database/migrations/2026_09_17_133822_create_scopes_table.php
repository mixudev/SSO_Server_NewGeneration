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
        Schema::create('scopes', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name', 128)->unique();
            $table->text('description')->nullable();
            $table->string('category', 32);
            $table->string('risk_level', 16);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_default')->default(false);
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->index(['status', 'risk_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scopes');
    }
};
