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
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->uuid('posted_by');
            $table->string('status', 10)->default('pending');
            $table->timestamp('posted_at')->useCurrent();
            $table->uuid('approved_by')->nullable();
            $table->timestamps();
            
            // Check constraint for status
            $table->check("status IN ('pending', 'approved', 'rejected')");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
