<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable()->index();
            $table->string('guest_token')->nullable()->unique()->comment('Para persistencia opcional con cookies');
            $table->foreignId('shared_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('cloned_from')->nullable()->constrained('carts')->onDelete('set null');
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_shared')->default(false);
            $table->boolean('is_saved')->default(false)->comment('Para carritos guardados permanentemente');
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['session_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
