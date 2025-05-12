<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plan_day_user', function (Blueprint $t) {
            $t->id();
            $t->foreignId('plan_user_id')
                ->constrained('plan_user')
                ->onDelete('cascade');

            $t->foreignId('plan_day_id')
                ->constrained()
                ->onDelete('cascade');
            $t->date('scheduled_date');
            $t->enum('status', ['pending', 'completed', 'missed'])->default('pending');
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();

            $t->unique(['plan_user_id', 'scheduled_date']);
            $t->index(['plan_user_id', 'status', 'scheduled_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_day_user');
    }
};
