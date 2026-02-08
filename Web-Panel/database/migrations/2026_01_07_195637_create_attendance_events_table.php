<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('attendance_device_id')->nullable()->constrained('attendance_devices');
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users');

            $table->string('event_type', 16);
            $table->dateTime('occurred_at');

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_events');
    }
};
