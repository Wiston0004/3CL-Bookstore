<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaign_promotions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();

            $t->enum('rule_type', ['percent_off','fixed_amount','buy_x_get_y','tiered']);
            $t->json('rule_config');              // {"percent":20} or {"buy":2,"get":1} or {"tiers":[...]}
            $t->unsignedSmallInteger('priority')->default(100);
            $t->boolean('stackable')->default(true);
            $t->boolean('active')->default(true);

            $t->timestamps();
            $t->softDeletes();

            $t->index(['campaign_id','priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_promotions');
    }
};
