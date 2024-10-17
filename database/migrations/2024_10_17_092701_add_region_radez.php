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
        Schema::table('radez_data', function (Blueprint $table) {
            $table->string('region_name')->nullable();
        });

        Schema::table('aztt_data', function (Blueprint $table) {
            $table->string('region_name')->nullable();
        });

        Schema::table('epidbiomed_data', function (Blueprint $table) {
            $table->string('region_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radez_data', function (Blueprint $table) {
            $table->dropColumn('region_name');
        });

        Schema::table('aztt_data', function (Blueprint $table) {
            $table->dropColumn('region_name');
        });

        Schema::table('epidbiomed_data', function (Blueprint $table) {
            $table->dropColumn('region_name');
        });
    }
};
