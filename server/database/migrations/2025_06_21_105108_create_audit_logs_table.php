<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
                Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->morphs('auditable'); 
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('action', 50)->index(); // Add length + index for better performance
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable(); // IPv6 support (39) + buffer
                $table->text('user_agent')->nullable(); // User agents can be long
                $table->string('table_name', 100)->nullable()->index(); // Optional: for easier querying
                $table->timestamps();
                
                // Optional: Add foreign key constraint
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
}
