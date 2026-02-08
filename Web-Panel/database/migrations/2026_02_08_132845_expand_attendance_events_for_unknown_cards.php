<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_events', function (Blueprint $table): void {
            $table->dropForeign(['employee_id']);
        });

        DB::statement('ALTER TABLE attendance_events MODIFY employee_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE attendance_events MODIFY event_type VARCHAR(32) NOT NULL');

        Schema::table('attendance_events', function (Blueprint $table): void {
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_events', function (Blueprint $table): void {
            $table->dropForeign(['employee_id']);
        });

        DB::statement('ALTER TABLE attendance_events MODIFY employee_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE attendance_events MODIFY event_type VARCHAR(10) NOT NULL');

        Schema::table('attendance_events', function (Blueprint $table): void {
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees');
        });
    }
};
