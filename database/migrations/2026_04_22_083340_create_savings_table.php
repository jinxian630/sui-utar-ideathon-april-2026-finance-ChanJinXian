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
        Schema::create('savings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('note')->nullable();
            $table->string('description')->nullable();
            $table->string('category')->nullable();
            $table->decimal('round_up_amount', 10, 4)->default(0);
            $table->boolean('synced_on_chain')->default(false);
            $table->string('sui_digest')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('round_up_streak')->default(0)->after('total_saved');
            $table->date('last_round_up_date')->nullable()->after('round_up_streak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['round_up_streak', 'last_round_up_date']);
        });

        Schema::dropIfExists('savings');
    }
};
