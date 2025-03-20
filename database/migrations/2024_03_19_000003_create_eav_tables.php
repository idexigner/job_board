<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateEavTables extends Migration
{
    public function up()
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['text', 'number', 'boolean', 'date', 'select']);
            $table->json('options')->nullable(); // For select type attributes
            $table->timestamps();

            // Indexes
            $table->unique('name');
            $table->index('type');
        });

        Schema::create('job_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs', 'id')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained()->onDelete('cascade');
            $table->text('value');
            $table->timestamps();

            // Indexes with limited length for text field
            $table->unique(['job_id', 'attribute_id']);


        });

         // Add indexes with proper length specifications
         DB::statement('ALTER TABLE job_attribute_values ADD INDEX jav_value_index (value(191))');
         DB::statement('ALTER TABLE job_attribute_values ADD INDEX jav_attribute_id_value_index (attribute_id, value(191))');
         DB::statement('ALTER TABLE job_attribute_values ADD INDEX jav_job_id_value_index (job_id, value(191))');
    }

    public function down()
    {
        Schema::dropIfExists('job_attribute_values');
        Schema::dropIfExists('attributes');
    }
}
