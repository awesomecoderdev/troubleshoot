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
        Schema::create("handymen", function (Blueprint $table) {
            $table->id();
            $table->integer("provider_id");
            $table->string("name");
            $table->string("email")->unique();
            $table->string("password");
            $table->string("phone");
            $table->string("image")->nullable();
            $table->text("address");
            $table->enum("status", ["available", "unavailable"])->default("available");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("handymen");
    }
};
