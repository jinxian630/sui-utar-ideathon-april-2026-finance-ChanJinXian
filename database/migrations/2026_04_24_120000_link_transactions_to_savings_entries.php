<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transaction', 'savings_entry_id')) {
            Schema::table('transaction', function (Blueprint $table) {
                $table->foreignId('savings_entry_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('savings_entries')
                    ->nullOnDelete();
            });
        }

        DB::table('transaction')
            ->whereNull('savings_entry_id')
            ->orderBy('id')
            ->get()
            ->each(function ($transaction) {
                $now = now();

                $entryId = DB::table('savings_entries')->insertGetId([
                    'user_id' => $transaction->user_id,
                    'goal_id' => null,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'round_up_amount' => 0,
                    'note' => $transaction->description,
                    'description' => $transaction->description,
                    'category' => 'other',
                    'synced_on_chain' => false,
                    'sui_digest' => null,
                    'staked' => false,
                    'stake_digest' => null,
                    'entry_date' => $now->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]);

                DB::table('transaction')
                    ->where('id', $transaction->id)
                    ->update(['savings_entry_id' => $entryId]);
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('transaction', 'savings_entry_id')) {
            $linkedEntryIds = DB::table('transaction')
                ->whereNotNull('savings_entry_id')
                ->pluck('savings_entry_id');

            DB::table('transaction')->update(['savings_entry_id' => null]);

            if ($linkedEntryIds->isNotEmpty()) {
                DB::table('savings_entries')
                    ->whereIn('id', $linkedEntryIds)
                    ->delete();
            }

            Schema::table('transaction', function (Blueprint $table) {
                $table->dropConstrainedForeignId('savings_entry_id');
            });
        }
    }
};
