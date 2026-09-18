<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event', 100);
            $table->string('request_id', 100)->nullable();
            $table->foreignId('organization_id')->nullable();
            $table->foreignId('application_id')->nullable();
            $table->string('subject', 191)->nullable();
            $table->string('actor', 191)->nullable();
            $table->string('risk', 16)->default('medium');
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['event', 'occurred_at']);
            $table->index(['organization_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_events');
    }
};
