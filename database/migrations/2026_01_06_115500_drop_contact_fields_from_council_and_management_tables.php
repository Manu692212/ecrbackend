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
            if (Schema::hasColumn('academic_councils', 'email')) {
                $table->dropUnique('academic_councils_email_unique');
                $table->dropColumn('email');
            }

            if (Schema::hasColumn('academic_councils', 'phone')) {
                $table->dropColumn('phone');
            }
        });

        Schema::table('management', function (Blueprint $table) {
            if (Schema::hasColumn('management', 'email')) {
                $table->dropUnique('management_email_unique');
                $table->dropColumn('email');
            }

            if (Schema::hasColumn('management', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_councils', function (Blueprint $table) {
            if (!Schema::hasColumn('academic_councils', 'email')) {
                $table->string('email')->nullable()->unique();
            }

            if (!Schema::hasColumn('academic_councils', 'phone')) {
                $table->string('phone')->nullable();
            }
        });

        Schema::table('management', function (Blueprint $table) {
            if (!Schema::hasColumn('management', 'email')) {
                $table->string('email')->nullable()->unique();
            }

            if (!Schema::hasColumn('management', 'phone')) {
                $table->string('phone')->nullable();
            }
        });
    }
};
