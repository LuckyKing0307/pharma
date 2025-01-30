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
        Schema::table('main_tablet_matrices', function (Blueprint $table) {
            $table->text('avromed')->nullable()->change();
            $table->text('azerimed')->nullable()->change();
            $table->text('aztt')->nullable()->change();
            $table->text('epidbiomed')->nullable()->change();
            $table->text('pasha-k')->nullable()->change();
            $table->text('radez')->nullable()->change();
            $table->text('sonar')->nullable()->change();
            $table->text('zeytun')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_tablet_matrices', function (Blueprint $table) {
            $table->string('avromed')->nullable()->change();
            $table->string('azerimed')->nullable()->change();
            $table->string('aztt')->nullable()->change();
            $table->string('epidbiomed')->nullable()->change();
            $table->string('pasha-k')->nullable()->change();
            $table->string('radez')->nullable()->change();
            $table->string('sonar')->nullable()->change();
            $table->string('zeytun')->nullable()->change();
        });
    }
};
