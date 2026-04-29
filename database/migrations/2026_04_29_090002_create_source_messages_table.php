<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('source_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_account_id')->constrained()->cascadeOnDelete();
            $table->string('source_type');
            $table->string('external_id');
            $table->string('external_parent_id')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_identifier')->nullable();
            $table->string('title')->nullable();
            $table->longText('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->json('metadata')->nullable();
            $table->string('source_url', 1024)->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->boolean('ai_processed')->default(false);
            $table->unsignedBigInteger('created_task_id')->nullable();
            $table->timestamps();

            $table->unique(['source_account_id', 'external_id'], 'sm_account_external_unique');
            $table->index(['ai_processed']);
            $table->index(['received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_messages');
    }
};
