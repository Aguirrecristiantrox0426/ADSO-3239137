<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rental_id')
                ->constrained('rentals')
                ->restrictOnDelete();

            $table->foreignId('advisor_id')
                ->constrained('advisors')
                ->restrictOnDelete();

            $table->date('return_date');

            $table->timestamps();

            $table->index(['rental_id', 'return_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};