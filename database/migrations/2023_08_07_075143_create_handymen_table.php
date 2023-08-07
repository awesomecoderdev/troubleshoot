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
        Schema::create('handymen', function (Blueprint $table) {
            $table->id();
            $table->integer("provider_id");
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('number');
            $table->string("image")->nullable();
            $table->text("address");
            $table->integer("rating")->default(0);
            $table->integer("rating_count")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('handymen');
    }
};
