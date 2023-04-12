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
        Schema::create('excel_details', function (Blueprint $table) {
            $table->id();
            $table->string('heading');
            $table->string('value')->nullable();
            $table->integer('row_number');
            $table->unsignedBigInteger('excel_file_id');
            $table->foreign('excel_file_id')->references('id')->on('excel_files')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excel_details');
    }
};
