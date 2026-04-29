<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_analysis_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_message_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider'); // openai|claude
            $table->string('model')->nullable();
            $table->string('prompt_hash', 64)->nullable();
            $table->json('response')->nullable();
            $table->boolean('is_task')->nullable();
            $table->float('confidence')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['provider', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_analysis_logs');
    }
};
