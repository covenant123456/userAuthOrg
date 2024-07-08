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
        // Creating the organisations table
        Schema::create('organisations', function (Blueprint $table) {
            $table->uuid('orgId')->primary();
            $table->uuid('userId'); // Adjust as per your business logic
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();

            // Optionally, add a foreign key constraint to link to users
            // $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
        });

        // Creating the organisation_user pivot table
        Schema::create('organisation_user', function (Blueprint $table) {
            $table->uuid('organisation_id');
            $table->uuid('user_id');
            $table->timestamps();

            // Setting up foreign keys
            $table->foreign('organisation_id')->references('orgId')->on('organisations')->onDelete('cascade');
            $table->foreign('user_id')->references('userId')->on('users')->onDelete('cascade');

            // Adding primary key for the pivot table
            $table->primary(['organisation_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dropping the pivot table first to avoid foreign key constraint issues
        Schema::dropIfExists('organisation_user');
        Schema::dropIfExists('organisations');
    }
};
