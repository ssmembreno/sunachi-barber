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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_id')->constrained('barber')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete(); // Optionally link a client
            $table->string('client_name')->nullable(); // In case it's a walk-in who doesn't want to register
            $table->decimal('amount', 8, 2); // To match the service price or a custom price
            $table->string('payment_method')->default('cash'); // cash, card, transfer, etc.
            $table->dateTime('sold_at')->nullable(); // Defaults to creation time, but allows setting custom
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['barber_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
