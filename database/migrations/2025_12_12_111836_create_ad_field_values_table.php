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
        Schema::create('ad_field_values', function (Blueprint $table) {
            $table->unsignedBigInteger('ad_id');
            $table->unsignedBigInteger('category_field_id');
            $table->unsignedBigInteger('category_field_option_id')->nullable();
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->foreign('ad_id')->references('id')->on('ads')->onDelete('cascade');
            $table->foreign('category_field_id')->references('id')->on('category_fields')->onDelete('cascade');
            $table->foreign('category_field_option_id')->references('id')->on('category_field_options')->onDelete('set null');
            $table->index('ad_id');
            $table->index('category_field_id');
            $table->unique(['ad_id', 'category_field_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_field_values');
    }
};
