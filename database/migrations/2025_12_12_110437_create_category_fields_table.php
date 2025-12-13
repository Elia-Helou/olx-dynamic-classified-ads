<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->index();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->string('external_id');
            $table->string('name');
            $table->string('field_type');
            $table->boolean('is_required')->default(false);
            $table->integer('min_value')->nullable();
            $table->integer('max_value')->nullable();
            $table->timestamps();
            $table->unique(['category_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_fields');
    }
};

