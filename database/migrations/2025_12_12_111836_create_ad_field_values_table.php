<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_field_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id')->index();
            $table->foreign('ad_id')->references('id')->on('ads')->onDelete('cascade');
            $table->unsignedBigInteger('category_field_id')->index();
            $table->foreign('category_field_id')->references('id')->on('category_fields')->onDelete('cascade');
            $table->unsignedBigInteger('category_field_option_id')->nullable()->index();
            $table->foreign('category_field_option_id')->references('id')->on('category_field_options')->onDelete('set null');
            $table->longText('value')->nullable();
            $table->timestamps();
            $table->unique(['ad_id', 'category_field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_field_values');
    }
};

