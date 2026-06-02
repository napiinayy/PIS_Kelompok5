<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7)->default('#4F46E5'); // hex color
            $table->string('icon', 10)->default('🏷️');      // emoji icon
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Pivot: bill can have many categories, category can have many bills
        Schema::create('bill_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['bill_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_category');
        Schema::dropIfExists('categories');
    }
};
