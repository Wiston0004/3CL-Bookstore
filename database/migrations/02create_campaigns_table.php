<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $t) {
            $t->id();
            $t->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();

            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();

            $t->timestamp('starts_at');
            $t->timestamp('ends_at')->nullable();
            $t->enum('status', ['draft','scheduled','live','completed','cancelled'])->default('draft')->index();

            $t->enum('visibility', ['public','private','targeted'])->default('public');
            $t->unsignedBigInteger('target_segment_id')->nullable()->index();

            $t->text('notes')->nullable();

            $t->timestamps();
            $t->softDeletes();

            $t->index(['starts_at','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
