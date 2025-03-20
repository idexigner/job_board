<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePivotTables extends Migration
{
    public function up()
    {
        // Create job_language pivot table
        Schema::create('job_language', function (Blueprint $table) {
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->primary(['job_id', 'language_id']);
        });

        // Create job_location pivot table
        Schema::create('job_location', function (Blueprint $table) {
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->primary(['job_id', 'location_id']);
        });

        // Create job_category pivot table
        Schema::create('job_category', function (Blueprint $table) {
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->primary(['job_id', 'category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_category');
        Schema::dropIfExists('job_location');
        Schema::dropIfExists('job_language');
    }
}
