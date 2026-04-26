<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('zk_pin_hash')->nullable()->after('sui_finance_profile_id');
            $table->timestamp('wallet_onboarded_at')->nullable()->after('zk_pin_hash');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['zk_pin_hash', 'wallet_onboarded_at']);
        });
    }
};
