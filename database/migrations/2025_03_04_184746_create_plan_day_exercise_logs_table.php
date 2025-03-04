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
        Schema::create('exercise_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_user_id')
                ->constrained('plan_user');
            $table->foreignId('plan_day_exercise_id')
                ->constrained('plan_day_exercises');
            $table->date('date');
            $table->integer('actual_sets')->nullable();
            $table->integer('actual_reps')->nullable();
            $table->float('weight_used')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_day_exercise_logs');
    }
};
