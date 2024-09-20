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
        Schema::create('epidbiomed_data', function (Blueprint $table) {
            $table->id();
            $table->string('aptek_name')->nullable();
            $table->string('tablet_name')->nullable();
            $table->string('qty')->nullable();
            $table->string('sales_qty')->nullable();
            $table->string('ost_qty')->nullable();
            $table->string('uploaded_file_id')->nullable();
            $table->dateTime('uploaded_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('epidbiomed_data');
    }
};
