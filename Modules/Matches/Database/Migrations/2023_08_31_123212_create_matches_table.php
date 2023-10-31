<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Matches\Enums\EMatchType as MatchTypeEnum;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            'matches', function (Blueprint $table) {
            $table->increments('id');
            $table->string('db_code');
            $table->string('label');
            $table->enum('match_type', array_column(MatchTypeEnum::cases(), 'name'));
            $table->unsignedSmallInteger('ordering');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
