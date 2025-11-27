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
        Schema::create('licences', function (Blueprint $table) {
            $table->id();
            $table->string('uld')->unique();            
            $table->string('wording');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('max_apps');
            $table->integer('max_executions_per_24h');
            $table->timestamp('valid_from');
            $table->timestamp('valid_to');
            $table->enum('status', ['ACTIVE', 'SUSPENDED', 'EXPIRED'])->default('ACTIVE');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('licences');
        Schema::enableForeignKeyConstraints();
    }
};
