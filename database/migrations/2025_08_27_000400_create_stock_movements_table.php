<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['restock','sale','adjustment']);
            $table->integer('quantity_change'); // signed delta
            $table->integer('resulting_stock');
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('stock_movements');
    }
};
