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
        Schema::create('category_field_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_field_id');
            $table->string('option_value');
            $table->string('option_label');
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->foreign('category_field_id')->references('id')->on('category_fields')->onDelete('cascade');
            $table->index('category_field_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_field_options');
    }
};
