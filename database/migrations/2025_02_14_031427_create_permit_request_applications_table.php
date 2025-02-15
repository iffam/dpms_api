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
        Schema::create('permit_request_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->enum('permit_type', ['restricted', 'temporary', 'permanent'])->default('permanent');
            $table->enum('status', ['approved', 'rejected', 'pending'])->default('pending');
            $table->text('status_remarks')->nullable();
            $table->dateTime('active_at')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->jsonb('zones')->nullable();
            $table->text('justification')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permit_request_applications');
    }
};
