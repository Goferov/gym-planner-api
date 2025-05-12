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
        Schema::table('exercises', function (Blueprint $t) {
            $t->string('image_path')->nullable()->after('video_url');
            $t->enum('preferred_media', ['image','video'])
                ->default('image')
                ->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $t) {
            $t->dropColumn(['image_path','preferred_media']);
        });
    }
};
