<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('restaurant_name')->nullable();
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('service_percent', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedInteger('times_used')->default(0);
            $table->timestamps();
        });

        Schema::create('bill_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_template_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_template_items');
        Schema::dropIfExists('bill_templates');
    }
};
