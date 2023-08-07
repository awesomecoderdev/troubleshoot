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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->integer("provider_id");
            $table->integer("address_id");
            $table->integer("customer_id");
            $table->integer("coupon_id")->default(0);
            $table->integer("handyman_id")->default(0);
            $table->integer("campaign_id")->default(0);
            $table->integer("service_id")->default(0);
            $table->integer("category_id")->default(0);
            $table->integer("zone_id")->default(0);
            $table->enum("status", ["pending", "accepted", "rejected", "progressing", "completed"])->default("pending");
            $table->boolean("is_paid")->default(false);
            $table->enum("payment_method", ["cod", "online"])->default("cod");
            $table->string("total_amount");
            $table->string("total_tax")->default("0");
            $table->string("total_discount")->default("0");
            $table->string("additional_charge")->default("0");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
