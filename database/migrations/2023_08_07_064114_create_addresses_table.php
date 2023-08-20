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
        Schema::create("addresses", function (Blueprint $table) {
            $table->id();
            $table->integer("customer_id");
            $table->string("street_one");
            $table->string("street_two")->nullable();
            $table->string("apartment_name");
            $table->string("apartment_number");
            $table->string("city");
            $table->string("zip");
            $table->string("lat")->nullable();
            $table->string("lng")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("addresses");
    }
};
