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
        Schema::create("campaigns", function (Blueprint $table) {
            $table->id();
            $table->integer("provider_id")->default(0);
            $table->integer("service_id")->default(0);
            $table->integer("category_id")->default(0);
            $table->integer("zone_id")->default(0);
            $table->string("name");
            $table->enum("type", ["category", "service"])->default("service");
            $table->integer("discount_percentage")->default(0);
            $table->timestamp("start")->nullable();
            $table->timestamp("end")->nullable();
            $table->string("image")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("campaigns");
    }
};
