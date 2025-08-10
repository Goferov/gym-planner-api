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
        // Nie ruszaj FK, jeśli testy lecą na sqlite
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('exercise_logs', function (Blueprint $t) {
            $t->dropForeign('exercise_logs_plan_day_exercise_id_foreign');
            $t->foreign('plan_day_exercise_id')
                ->references('id')->on('plan_day_exercises')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('exercise_logs', function (Blueprint $t) {
            $t->dropForeign(['plan_day_exercise_id']);
            $t->foreign('plan_day_exercise_id')
                ->references('id')->on('plan_day_exercises');
        });
    }

};
