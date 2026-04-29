<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('status')->default('inbox'); // inbox|todo|in_progress|waiting|done|ignored
            $table->string('priority')->default('normal'); // low|normal|high|urgent
            $table->date('due_date')->nullable();

            $table->string('source_type')->default('manual'); // manual|gmail|slack|telegram|monday|wrike
            $table->foreignId('source_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_account_label')->nullable();
            $table->string('source_external_id')->nullable();
            $table->string('source_parent_external_id')->nullable();
            $table->string('source_url', 1024)->nullable();

            $table->longText('context_text')->nullable();
            $table->longText('ai_summary')->nullable();
            $table->longText('ai_reasoning_short')->nullable();
            $table->longText('follow_up_suggestion')->nullable();
            $table->float('ai_confidence')->nullable();

            $table->string('external_sync_status')->default('not_synced'); // not_synced|synced|sync_failed|imported_only
            $table->timestamp('external_last_synced_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->boolean('needs_review')->default(false);

            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index(['source_type']);
            $table->index(['due_date']);
            $table->index(['source_account_id', 'source_external_id'], 'tasks_src_acc_ext_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
