<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_credentials', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('application_id')->unique()->constrained()->cascadeOnDelete();
            $table->uuid('passport_client_id')->unique();
            $table->string('status', 32)->default('active');
            $table->unsignedInteger('generation')->default(1);
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_credentials');
    }
};
