<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $t->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();

            $t->string('title');
            $t->longText('body');

            $t->json('channels')->nullable();     // ["mail","sms","push"]

            // Targeting snapshot
            $t->enum('target_type', ['all','role','segment','users'])->default('all');
            $t->json('target_value')->nullable(); // {"role":"customer"} / {"segment_id":3} / {"user_ids":[...]}

            $t->timestamp('scheduled_at')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->enum('status', ['draft','scheduled','sent','failed','cancelled'])->default('draft')->index();

            $t->unsignedInteger('send_count')->default(0);
            $t->unsignedInteger('fail_count')->default(0);

            $t->timestamps();
            $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
