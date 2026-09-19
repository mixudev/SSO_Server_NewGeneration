<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_user_access', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->unique(['application_id', 'user_id']);
            $table->index(['user_id', 'status']);
            $table->index(['application_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_user_access');
    }
};
