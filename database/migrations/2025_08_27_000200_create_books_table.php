<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('isbn')->nullable()->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('price_cents')->default(0);
            $table->string('cover_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('book_category', function (Blueprint $table) {
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['book_id','category_id']);
        });

        Schema::create('book_tag', function (Blueprint $table) {
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['book_id','tag_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('book_tag');
        Schema::dropIfExists('book_category');
        Schema::dropIfExists('books');
    }
};
