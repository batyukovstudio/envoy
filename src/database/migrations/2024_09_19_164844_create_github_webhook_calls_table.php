<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('github_webhook_calls', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('url');
            $table->string('name');
            $table->json('headers')->nullable();
            $table->json('payload')->nullable();
            $table->text('exception')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('github_webhook_calls');
    }
};
