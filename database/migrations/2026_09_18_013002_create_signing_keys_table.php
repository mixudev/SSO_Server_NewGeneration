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
        Schema::create('signing_keys', function (Blueprint $table): void {
            $table->id();
            $table->string('kid', 64)->unique();
            $table->string('algorithm', 16)->default('RS256');
            $table->text('public_key');
            $table->text('private_key');
            $table->string('status', 16)->default('active');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('retired_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'algorithm']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signing_keys');
    }
};
