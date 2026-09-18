<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('security_events', function (Blueprint $table): void {
            $table->dropIndex('security_events_organization_id_occurred_at_index');
            $table->dropColumn(['organization_id', 'application_id']);
        });

        Schema::table('security_events', function (Blueprint $table): void {
            $table->string('organization_id', 26)->nullable()->after('request_id');
            $table->string('application_id', 26)->nullable()->after('organization_id');
            $table->index(['organization_id', 'occurred_at']);
            $table->index(['application_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::table('security_events', function (Blueprint $table): void {
            $table->dropIndex('security_events_organization_id_occurred_at_index');
            $table->dropIndex('security_events_application_id_occurred_at_index');
            $table->dropColumn(['organization_id', 'application_id']);
        });

        Schema::table('security_events', function (Blueprint $table): void {
            $table->foreignId('organization_id')->nullable()->after('request_id');
            $table->foreignId('application_id')->nullable()->after('organization_id');
        });
    }
};
