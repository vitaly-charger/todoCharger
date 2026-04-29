<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('source_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // gmail|slack|telegram|monday|wrike
            $table->string('name');
            $table->string('identifier')->nullable();
            $table->text('credentials')->nullable(); // encrypted (cast)
            $table->json('settings')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status')->nullable();
            $table->timestamps();

            $table->index(['type', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_accounts');
    }
};
