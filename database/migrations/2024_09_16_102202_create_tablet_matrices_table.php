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
        Schema::create('tablet_matrices', function (Blueprint $table) {
            $table->id();
            $table->string('avromed')->nullable();
            $table->string('azerimed')->nullable();
            $table->string('aztt')->nullable();
            $table->string('epidbiomed')->nullable();
            $table->string('pasha-k')->nullable();
            $table->string('radez')->nullable();
            $table->string('sonar')->nullable();
            $table->string('zeytun')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tablet_matrices');
    }
};
