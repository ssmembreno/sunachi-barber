<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_at');
            $table->dateTime('end_at');

            $table->string('status', 20)->default('pending');
            $table->string('source', 10)->default('web');

            $table->text('notes')->nullable();
            $table->text('client_notes')->nullable();

            $table->unsignedInteger('price')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();

            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('barber_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['barber_id', 'start_at']);
            $table->index(['client_id', 'start_at']);
            $table->index(['start_at', 'end_at']);
            $table->index(['status', 'start_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

