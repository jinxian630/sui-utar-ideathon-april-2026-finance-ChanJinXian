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
        Schema::table('badges', function (Blueprint $table) {
            if (!Schema::hasColumn('badges', 'sui_object_id')) {
                $table->string('sui_object_id')->nullable()->after('sui_digest');
            }

            if (!Schema::hasColumn('badges', 'level')) {
                $table->unsignedTinyInteger('level')->nullable()->after('threshold');
            }

            if (!Schema::hasColumn('badges', 'suivision_url')) {
                $table->string('suivision_url')->nullable()->after('sui_object_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('badges', 'suivision_url') ? 'suivision_url' : null,
                Schema::hasColumn('badges', 'sui_object_id') ? 'sui_object_id' : null,
                Schema::hasColumn('badges', 'level') ? 'level' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
