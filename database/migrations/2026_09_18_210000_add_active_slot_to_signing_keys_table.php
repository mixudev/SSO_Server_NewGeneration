<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signing_keys', function (Blueprint $table): void {
            $table->unsignedTinyInteger('active_slot')->nullable()->after('status');
        });

        $active = DB::table('signing_keys')
            ->where('algorithm', 'RS256')
            ->where('status', 'active')
            ->orderByDesc('activated_at')
            ->orderByDesc('id')
            ->get();

        foreach ($active as $index => $key) {
            DB::table('signing_keys')->where('id', $key->id)->update([
                'status' => $index === 0 ? 'active' : 'retired',
                'active_slot' => $index === 0 ? 1 : null,
                'retired_at' => $index === 0 ? null : now(),
            ]);
        }

        DB::table('signing_keys')->where('status', 'retired')->update(['active_slot' => null]);

        Schema::table('signing_keys', function (Blueprint $table): void {
            $table->unique(['algorithm', 'active_slot'], 'signing_keys_algorithm_active_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::table('signing_keys', function (Blueprint $table): void {
            $table->dropUnique('signing_keys_algorithm_active_slot_unique');
            $table->dropColumn('active_slot');
        });
    }
};
