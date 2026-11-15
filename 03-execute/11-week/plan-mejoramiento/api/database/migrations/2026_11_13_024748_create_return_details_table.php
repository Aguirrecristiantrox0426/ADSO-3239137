<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('return_id')
                ->constrained('returns')
                ->restrictOnDelete();

            $table->foreignId('rental_detail_id')
                ->constrained('rental_details')
                ->restrictOnDelete();

            $table->unsignedInteger('returned_quantity');

            $table->timestamps();

            $table->index(['return_id', 'rental_detail_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_details');
    }
};