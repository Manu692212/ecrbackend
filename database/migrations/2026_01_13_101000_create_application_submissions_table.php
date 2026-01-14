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
        Schema::create('application_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('form_type'); // course, career, job, enquiry, etc.
            $table->string('full_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('title')->nullable(); // course title, role, etc.
            $table->string('status')->default('new'); // new, in_review, contacted, closed
            $table->json('payload')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('admin_viewed_at')->nullable();
            $table->timestamps();

            $table->index(['form_type', 'status']);
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_submissions');
    }
};
