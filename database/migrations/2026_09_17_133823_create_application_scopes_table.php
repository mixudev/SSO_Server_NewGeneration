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
        Schema::create('application_scopes', function (Blueprint $table): void {
            $table->id();
            $table->foreignUlid('application_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('scope_id')->constrained()->cascadeOnDelete();
            $table->boolean('allowed')->default(true);
            $table->boolean('consent_required')->default(true);
            $table->timestamps();

            $table->unique(['application_id', 'scope_id']);
            $table->index(['application_id', 'allowed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_scopes');
    }
};
