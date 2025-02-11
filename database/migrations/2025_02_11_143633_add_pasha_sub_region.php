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
        Schema::table('pasha_data', function (Blueprint $table) {
            $table->text('main_parent')->nullable();
        });
        Schema::table('region_matrices', function (Blueprint $table) {
            $table->string('pasha_extra')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasha_data', function (Blueprint $table) {
            $table->dropColumn('main_parent');
        });
        Schema::table('region_matrices', function (Blueprint $table) {
            $table->dropColumn('pasha_extra');
        });
    }
};
