<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rental_id')
                ->constrained('rentals')
                ->restrictOnDelete();

            $table->foreignId('tool_id')
                ->constrained('tools')
                ->restrictOnDelete();

            $table->string('tool_name_snapshot');
            $table->unsignedInteger('quantity');

            $table->timestamps();

            $table->index(['rental_id', 'tool_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_details');
    }
};