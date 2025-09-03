<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_registrations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Contact snapshot (for guests or changing contact later)
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();

            $t->enum('status', ['registered','checked_in','cancelled','no_show'])
              ->default('registered')->index();

            // Award tracking (idempotent points credit)
            $t->unsignedInteger('awarded_points')->default(0);
            $t->timestamp('awarded_at')->nullable();

            $t->timestamp('registered_at')->nullable();
            $t->timestamp('checked_in_at')->nullable();

            $t->string('source', 32)->nullable(); // web, email, qr, admin

            // Token for guest self-service / QR check-in
            $t->string('token', 64)->nullable()->unique();

            $t->timestamps();
            $t->softDeletes();

            // Prevent duplicate registrations per user (guests allowed via NULL user_id)
            $t->unique(['event_id','user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
