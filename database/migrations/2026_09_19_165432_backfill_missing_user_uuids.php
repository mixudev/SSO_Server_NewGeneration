<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('uuid')
            ->orderBy('id')
            ->eachById(function (object $user): void {
                DB::table('users')
                    ->where('id', $user->id)
                    ->whereNull('uuid')
                    ->update(['uuid' => (string) Str::uuid()]);
            });
    }

    public function down(): void
    {
        // UUIDs are identity data and must not be cleared during rollback.
    }
};
