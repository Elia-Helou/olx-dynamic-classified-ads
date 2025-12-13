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
            $table->integer('olx_id')->index();
            $table->unsignedBigInteger('category_field_id')->index();
            $table->foreign('category_field_id')->references('id')->on('category_fields')->onDelete('cascade');
            $table->string('option_value');
            $table->string('option_label');
            $table->integer('parent_olx_id')->nullable()->index();
            $table->timestamps();
            $table->unique(['olx_id', 'category_field_id']);
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
