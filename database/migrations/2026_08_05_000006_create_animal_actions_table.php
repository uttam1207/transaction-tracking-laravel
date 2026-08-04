<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animal_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained('animals')->onDelete('cascade');
            $table->enum('action_type', [
                'Vaccination',
                'Deworming',
                'Heat Detection',
                'AI',
                'Pregnancy Check',
                'Calving',
                'Dry Off',
                'Sale',
                'Death'
            ]);
            $table->date('action_date');
            $table->decimal('cost', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animal_actions');
    }
};
