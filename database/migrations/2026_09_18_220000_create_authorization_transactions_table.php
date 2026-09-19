<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authorization_transactions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->char('transaction_id_hash', 64)->unique();
            $table->uuid('client_id');
            $table->foreignUlid('application_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->char('redirect_uri_hash', 64);
            $table->string('response_type', 32);
            $table->text('scope_string');
            $table->char('state_hash', 64)->nullable();
            $table->char('nonce_hash', 64)->nullable();
            $table->string('code_challenge', 128);
            $table->string('code_challenge_method', 16);
            $table->string('status', 32)->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('authenticated_at')->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status', 'expires_at']);
            $table->index(['application_id', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authorization_transactions');
    }
};
