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
        Schema::table('academic_councils', function (Blueprint $table) {
            $table->string('designation')->nullable();
            $table->text('qualifications')->nullable();
            $table->string('image_size')->default('medium'); // small, medium, large
            $table->integer('image_width')->nullable();
            $table->integer('image_height')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_councils', function (Blueprint $table) {
            $table->dropColumn('designation');
            $table->dropColumn('qualifications');
            $table->dropColumn('image_size');
            $table->dropColumn('image_width');
            $table->dropColumn('image_height');
        });
    }
};
