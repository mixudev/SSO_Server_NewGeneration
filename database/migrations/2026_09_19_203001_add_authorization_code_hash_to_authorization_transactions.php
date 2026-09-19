<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('authorization_transactions', function (Blueprint $table): void {
            $table->char('authorization_code_hash', 64)->nullable()->unique()->after('transaction_id_hash');
        });
    }

    public function down(): void
    {
        Schema::table('authorization_transactions', function (Blueprint $table): void {
            $table->dropUnique(['authorization_code_hash']);
            $table->dropColumn('authorization_code_hash');
        });
    }
};
