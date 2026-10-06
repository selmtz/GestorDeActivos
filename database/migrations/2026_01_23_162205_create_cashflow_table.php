<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashflow', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['buy', 'sell', 'deposit', 'withdraw']);
            $table->foreignId('asset_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 18, 8)->nullable();
            $table->decimal('price_usd', 16, 8)->nullable();
            $table->decimal('total_usd', 16, 8);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashflows');
    }
};
