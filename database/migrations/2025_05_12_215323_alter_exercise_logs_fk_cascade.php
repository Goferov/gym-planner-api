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
        // 1) usuń stary FK
        Schema::table('exercise_logs', function (Blueprint $t) {
            $t->dropForeign('exercise_logs_plan_day_exercise_id_foreign');
        });

        // 2) dodaj nowy z CASCADE
        Schema::table('exercise_logs', function (Blueprint $t) {
            $t->foreign('plan_day_exercise_id')
                ->references('id')->on('plan_day_exercises')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // przy rollbacku: przywróć bez cascade
        Schema::table('exercise_logs', function (Blueprint $t) {
            $t->dropForeign(['plan_day_exercise_id']);
            $t->foreign('plan_day_exercise_id')
                ->references('id')->on('plan_day_exercises');
        });
    }
};
