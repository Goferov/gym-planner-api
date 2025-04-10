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
        Schema::table('exercise_logs', function (Blueprint $table) {
            $table->boolean('completed')->default(false)->after('plan_day_exercise_id');

            $table->tinyInteger('difficulty_reported')
                ->nullable()
                ->after('completed');

            $table->text('difficulty_comment')
                ->nullable()
                ->after('difficulty_reported');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercise_logs', function (Blueprint $table) {
            $table->dropColumn('difficulty_comment');
            $table->dropColumn('difficulty_reported');
            $table->dropColumn('completed');
        });
    }
};
