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
        if (!Schema::hasColumn('badges', 'slug')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->dropColumn('streak_days');
                $table->string('slug')->after('user_id');
                $table->decimal('threshold', 15, 2)->after('name');
                $table->unique(['user_id', 'slug']);
            });
        }

        if (!Schema::hasColumn('users', 'total_saved')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('total_saved', 15, 2)->default(0)->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('total_saved');
        });

        Schema::table('badges', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'slug']);
            $table->dropColumn(['slug', 'threshold']);
            $table->integer('streak_days')->after('name');
        });
    }
};
