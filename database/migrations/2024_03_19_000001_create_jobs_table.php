<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('company_name');
            $table->decimal('salary_min', 10, 2);
            $table->decimal('salary_max', 10, 2);
            $table->boolean('is_remote')->default(false);
            $table->enum('job_type', ['full-time', 'part-time', 'contract', 'freelance']);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Single column indexes
            $table->index('title');
            $table->index('company_name');
            $table->index('salary_min');
            $table->index('salary_max');
            $table->index('is_remote');
            $table->index('job_type');
            $table->index('status');
            $table->index('published_at');

            // Composite indexes for common filter combinations
            $table->index(['job_type', 'is_remote']);
            $table->index(['status', 'published_at']);
            $table->index(['salary_min', 'salary_max']);
            $table->index(['company_name', 'job_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}
