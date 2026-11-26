<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('advisor')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
            $table->index(['role', 'is_active']);
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); $table->string('name')->unique(); $table->text('description')->nullable(); $table->timestamps(); $table->softDeletes();
        });
        Schema::table('advisors', function (Blueprint $table) { $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained()->nullOnDelete(); });
        Schema::table('tools', function (Blueprint $table) { $table->foreignId('category_id')->nullable()->after('id')->constrained()->nullOnDelete(); });
    }
    public function down(): void
    {
        Schema::table('tools', fn (Blueprint $table) => $table->dropConstrainedForeignId('category_id'));
        Schema::table('advisors', fn (Blueprint $table) => $table->dropConstrainedForeignId('user_id'));
        Schema::dropIfExists('categories');
        Schema::table('users', function (Blueprint $table) { $table->dropIndex(['role', 'is_active']); $table->dropColumn(['role', 'is_active']); });
    }
};
