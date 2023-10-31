<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            'matches_values', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('matches_profile_id');
            $table->unsignedInteger('matches_id');
            $table->string('value')->nullable();
            $table->unsignedTinyInteger('order')->nullable()->default(null);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('matches_profile_id')
                ->references('id')
                ->on('matches_profile')
                ->onDelete('cascade');
            $table->foreign('matches_id')
                ->references('id')
                ->on('matches')
                ->onDelete('cascade');
        }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches_options');
    }
};
