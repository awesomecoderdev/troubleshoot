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
        Schema::create("customers", function (Blueprint $table) {
            $table->id();
            $table->string("first_name")->nullable();
            $table->string("last_name")->nullable();
            $table->string("email")->unique();
            $table->string("password");
            $table->string("phone", 20)->unique();
            $table->boolean("phone_verify")->default(false);
            $table->string("ref")->unique()->nullable();
            $table->integer("otp");
            $table->string("image")->nullable();
            $table->boolean("status")->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("customers");
    }
};
