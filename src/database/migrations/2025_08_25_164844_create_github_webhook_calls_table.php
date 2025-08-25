<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gitlab_webhook_calls', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->json('headers')->nullable();
            $table->text('exception')->nullable();
            $table->json('payload');
            $table->integer('tries')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }
};
