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
        Schema::create('avromed_data', function (Blueprint $table) {
            $table->id();
            $table->string('branch')->nullable();
            $table->string('date')->nullable();
            $table->string('main_parent')->nullable();
            $table->string('main_supplier')->nullable();
            $table->string('region')->nullable();
            $table->string('region_name')->nullable();
            $table->string('aptek_name')->nullable();
            $table->string('tablet_name')->nullable();
            $table->string('supervisor')->nullable();
            $table->string('item_code')->nullable();
            $table->string('client_code')->nullable();
            $table->string('sales_qty')->nullable();
            $table->string('new_sales')->nullable();
            $table->string('uploaded_file_id')->nullable();
            $table->dateTime('sale_date')->nullable();
            $table->dateTime('uploaded_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avromed_data');
    }
};
