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
        Schema::create('desk365_api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10); // GET, POST, PUT, DELETE, etc.
            $table->text('endpoint'); // Full URL endpoint
            $table->json('request_headers')->nullable(); // Request headers (sanitized)
            $table->longText('request_body')->nullable(); // Request payload/body
            $table->integer('response_status')->nullable(); // HTTP status code
            $table->longText('response_body')->nullable(); // Response body
            $table->integer('duration_ms')->nullable(); // Request duration in milliseconds
            $table->text('error_message')->nullable(); // Error message if request failed
            $table->string('operation')->nullable(); // Operation name (e.g., 'createTicket', 'updateTicket')
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('method');
            $table->index('response_status');
            $table->index('operation');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desk365_api_logs');
    }
};

