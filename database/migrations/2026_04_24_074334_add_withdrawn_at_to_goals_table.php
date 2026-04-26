<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            // Track when a completed goal was withdrawn
            $table->timestamp('withdrawn_at')->nullable()->after('is_active');
        });

        // Add wallet_balance column to users (separate from total_saved which will become the goal pool total)
        if (!Schema::hasColumn('users', 'wallet_balance')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('wallet_balance', 15, 2)->default(0)->after('total_saved');
            });
        }

        // Track rebate amount earned from on-chain milestones
        if (!Schema::hasColumn('users', 'rebate_earned')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('rebate_earned', 15, 2)->default(0)->after('wallet_balance');
            });
        }
    }

    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn('withdrawn_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['wallet_balance', 'rebate_earned']);
        });
    }
};
