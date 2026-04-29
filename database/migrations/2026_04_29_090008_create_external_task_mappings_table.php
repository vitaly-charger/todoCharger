<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('external_task_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('provider'); // monday|wrike
            $table->foreignId('source_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_id');
            $table->string('external_parent_id')->nullable();
            $table->string('external_url', 1024)->nullable();
            $table->string('sync_status')->default('synced');
            $table->timestamp('last_synced_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'external_id'], 'etm_provider_external_unique');
            $table->index(['task_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_task_mappings');
    }
};
