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
        Schema::create("services", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->integer("subcategory_id")->default(0);
            $table->integer("category_id");
            $table->integer("provider_id");
            $table->integer("zone_id");
            $table->string("price");
            $table->enum("type", ["fixed", "hourly"])->default("fixed");
            $table->string("duration");
            $table->string("image")->nullable();
            $table->double("discount");
            $table->boolean("status")->default(true);
            $table->text("short_description")->nullable();
            $table->text("long_description")->nullable();
            $table->float("tax")->default(0);
            $table->integer("order_count")->default(0);
            $table->integer("rating_count")->default(0);
            $table->float("avg_rating")->default(0);
            $table->boolean("is_featured")->default(false);
            $table->boolean("by_admin")->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("services");
    }
};
