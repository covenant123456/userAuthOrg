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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('userId')->primary()->default('');
            $table->string('firstName');
            $table->string('lastName');
            $table->string('email')->unique();
            $table->string('name')->default('');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
        
        
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary(); // Primary key is the email address
            $table->string('token'); // Token for resetting password
            $table->timestamp('created_at')->nullable(); // Timestamp when token was created
        });
        

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // Primary key, session ID
            $table->foreignId('user_id')->nullable()->index(); // Foreign key to users table
            $table->string('ip_address', 45)->nullable(); // IP address of the user's session
            $table->text('user_agent')->nullable(); // User agent string
            $table->longText('payload'); // Session data payload
            $table->integer('last_activity')->index(); // Timestamp of last activity
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
