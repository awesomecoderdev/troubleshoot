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
        Schema::create("providers", function (Blueprint $table) {
            $table->id();
            $table->integer("zone_id");
            $table->string("company_name")->nullable();
            $table->string("first_name")->nullable();
            $table->string("last_name")->nullable();
            $table->string("email")->unique();
            $table->string("password");
            $table->string("phone", 15)->unique();
            $table->string("identity_number");
            $table->string("contact_person_name");
            $table->string("contact_person_phone");
            $table->string("account_email");
            $table->string("image")->nullable();
            $table->text("identity_image")->nullable();
            $table->integer("order_count")->default(0);
            $table->integer("service_man_count")->default(0);
            $table->integer("service_capacity_per_day")->default(0);
            $table->integer("rating_count")->default(0);
            $table->string("avg_rating")->default(0);
            $table->boolean("commission_status")->default(false);
            $table->string("commission_percentage")->default(0);
            $table->boolean("is_active")->default(true);
            $table->boolean("is_approved")->default(false);
            $table->timestamp("start");
            $table->timestamp("end");
            $table->text("off_day")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("providers");
    }
};
