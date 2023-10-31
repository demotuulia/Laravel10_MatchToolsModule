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
        Schema::table('users', function ($table) {
            $table->string('familyName')->nullable();
            $table->string('prefix')->nullable();
            $table->string('street')->nullable();
            $table->string('houseNumber')->nullable();
            $table->string('postalCode')->nullable();
            $table->string('city')->nullable();
            $table->string('tel')->nullable();
            $table->string('company')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('familyName');
            $table->dropColumn('prefix');
            $table->dropColumn('street');
            $table->dropColumn('houseNumber');
            $table->dropColumn('postalCode');
            $table->dropColumn('city');
            $table->dropColumn('tel');
        });
    }
};
