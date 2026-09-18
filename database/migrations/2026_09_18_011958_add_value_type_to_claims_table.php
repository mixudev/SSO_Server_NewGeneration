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
        Schema::table('claims', function (Blueprint $table): void {
            $table->string('value_type', 16)->default('string')->after('source');
            $table->index('value_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table): void {
            $table->dropIndex(['value_type']);
            $table->dropColumn('value_type');
        });
    }
};
