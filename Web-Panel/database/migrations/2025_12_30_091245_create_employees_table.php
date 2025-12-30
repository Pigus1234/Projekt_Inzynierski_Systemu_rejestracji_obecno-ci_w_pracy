<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table): void {
            $table->id();
            $table->string('rfid_uid')->unique();
            $table->string('full_name');
            $table->string('department')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['full_name']);
            $table->index(['department']);
            $table->index(['deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
