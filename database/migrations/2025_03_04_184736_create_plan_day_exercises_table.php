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
        Schema::create('plan_day_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_day_id')
                ->constrained('plan_days');
            $table->foreignId('exercise_id')
                ->constrained('exercises');
            $table->integer('sets')->nullable();
            $table->integer('reps')->nullable();
            $table->integer('rest_time')->nullable();
            $table->string('tempo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_day_exercises');
    }
};
