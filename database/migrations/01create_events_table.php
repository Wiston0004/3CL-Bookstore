<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organizer_id')->nullable()->constrained('users')->nullOnDelete();

            $t->string('title');
            $t->string('slug')->unique();
            $t->text('description')->nullable();

            $t->enum('type', ['book_fair','flash_sale','author_lecture','webinar','other'])->default('other');
            $t->enum('delivery_mode', ['onsite','online','hybrid'])->default('online');

            $t->string('timezone', 64)->default('Asia/Kuala_Lumpur');
            $t->timestamp('starts_at');
            $t->timestamp('ends_at')->nullable();

            // Onsite
            $t->string('venue_name')->nullable();
            $t->string('address')->nullable();
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();

            // Online
            $t->string('join_url')->nullable();

            // Visibility / targeting (segment optional)
            $t->enum('visibility', ['public','private','targeted'])->default('public');
            $t->unsignedBigInteger('target_segment_id')->nullable()->index();

            // Lifecycle
            $t->enum('status', ['draft','scheduled','live','completed','cancelled'])->default('draft')->index();
            $t->text('cancellation_reason')->nullable();

            // Registration
            $t->boolean('registration_required')->default(true);
            $t->unsignedInteger('max_attendees')->nullable();

            // Points reward per join
            $t->unsignedInteger('points_reward')->default(0);

            $t->string('banner_path')->nullable();

            $t->timestamps();
            $t->softDeletes();

            $t->index(['starts_at','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
