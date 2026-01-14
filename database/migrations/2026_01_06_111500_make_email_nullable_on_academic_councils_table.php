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
        // Email column is already unique from the initial migration
        // This column will be dropped in a later migration, so skip the modification
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
