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
        Schema::rename('savings', 'savings_entries');

        Schema::table('savings_entries', function (Blueprint $table) {
            $table->foreignId('goal_id')->nullable()->after('user_id')->constrained('goals')->nullOnDelete();
            $table->enum('type', ['income', 'expense'])->default('income')->after('goal_id');
            $table->boolean('staked')->default(false)->after('sui_digest');
            $table->string('stake_digest')->nullable()->after('staked');
            $table->date('entry_date')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_DATE'))->after('stake_digest');
            
            $table->index(['user_id', 'entry_date']);
            $table->index(['user_id', 'goal_id']);
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings_entries', function (Blueprint $table) {
            $table->dropForeign(['goal_id']);
            $table->dropIndex(['user_id', 'entry_date']);
            $table->dropIndex(['user_id', 'goal_id']);
            $table->dropIndex(['user_id', 'type']);
            $table->dropColumn(['goal_id', 'type', 'staked', 'stake_digest', 'entry_date']);
        });

        Schema::rename('savings_entries', 'savings');
    }
};
