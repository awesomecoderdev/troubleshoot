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
        Schema::create("coupons", function (Blueprint $table) {
            $table->id();
            $table->integer("provider_id");
            $table->string("name");
            $table->string("code")->unique();
            $table->integer("discount");
            $table->timestamp("start");
            $table->timestamp("end");
            $table->integer("min_amount");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("coupons");
    }
};
