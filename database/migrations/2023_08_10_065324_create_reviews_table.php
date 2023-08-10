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
        Schema::create("reviews", function (Blueprint $table) {
            $table->id();
            $table->integer("booking_id")->default(0);
            $table->integer("service_id")->default(0);
            $table->integer("provider_id")->default(0);
            $table->string("customer_name");
            $table->integer("customer_id");
            $table->integer("review_rating")->default(1);
            $table->string("review_comment")->nullable();
            $table->timestamp("booking_date")->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("reviews");
    }
};
