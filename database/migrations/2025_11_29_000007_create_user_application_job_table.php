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
        Schema::create('user_application_job', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('application_id')->constrained('applications')->onDelete('cascade');
            $table->foreignId('job_application_id')->constrained('job_applications')->onDelete('cascade');
            $table->boolean('is_active')->default(true); // Pour la fenêtre glissante
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['user_id', 'application_id', 'job_application_id'], 'user_app_job_idx');
            $table->index(['user_id', 'application_id'], 'user_app_idx');
            $table->index('created_at', 'created_at_idx');
            $table->index('is_active', 'is_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_application_job');
        Schema::enableForeignKeyConstraints();
    }
};
