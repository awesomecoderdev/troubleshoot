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
        Schema::create("schedules", function (Blueprint $table) {
            $table->id();
            $table->integer("handyman_id");
            $table->integer("booking_id");
            $table->timestamp("date");
            $table->timestamp("start");
            $table->timestamp("end");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("schedules");
    }
};
