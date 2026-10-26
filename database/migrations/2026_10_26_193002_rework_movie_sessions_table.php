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
        /*
         * Overlaps are checked by the application, so the unique index is not
         * needed: it would also stop a new session from reusing the minute of a
         * cancelled one.
         */
        Schema::table('movie_sessions', function (Blueprint $table) {
            $table->dropUnique(['hall_id', 'starts_at']);
        });

        /*
         * The end is calculated from the film duration instead of being stored,
         * so it can never disagree with the film card.
         */
        Schema::table('movie_sessions', function (Blueprint $table) {
            $table->dropColumn('ends_at');
        });

        Schema::table('movie_sessions', function (Blueprint $table) {
            $table->index(['hall_id', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movie_sessions', function (Blueprint $table) {
            $table->dropIndex(['hall_id', 'starts_at']);
        });

        Schema::table('movie_sessions', function (Blueprint $table) {
            $table->dateTime('ends_at')->nullable()->after('starts_at');
            $table->unique(['hall_id', 'starts_at']);
        });
    }
};
