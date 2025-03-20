<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\Language;
use App\Models\Location;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\JobAttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Faker\Factory as Faker;

class JobSeeder extends Seeder
{
    public function run()
    {
        try {
            DB::beginTransaction();

            $faker = Faker::create();

            // Get all IDs for relationships
            $languages = Language::all();
            if ($languages->isEmpty()) {
                throw new Exception('No languages found in the database. Please run LanguageSeeder first.');
            }

            $locations = Location::all();
            if ($locations->isEmpty()) {
                throw new Exception('No locations found in the database. Please run LocationSeeder first.');
            }

            $categories = Category::all();
            if ($categories->isEmpty()) {
                throw new Exception('No categories found in the database. Please run CategorySeeder first.');
            }

            $attributes = Attribute::all();
            if ($attributes->isEmpty()) {
                throw new Exception('No attributes found in the database. Please run AttributeSeeder first.');
            }

            // Company names pool
            $companies = [
                'TechCorp', 'InnovateSoft', 'DataDynamics', 'CloudNine Solutions',
                'ByteBridge', 'QuantumCode', 'CyberSys', 'DevFlow', 'SmartStack',
                'FutureTech'
            ];

            // Create 500 jobs
            for ($i = 0; $i < 500; $i++) {
                try {
                    $job = Job::create([
                        'title' => $faker->jobTitle,
                        'description' => $faker->paragraphs(3, true),
                        'company_name' => $faker->randomElement($companies),
                        'salary_min' => $faker->numberBetween(40000, 80000),
                        'salary_max' => $faker->numberBetween(81000, 150000),
                        'is_remote' => $faker->boolean(30),
                        'job_type' => $faker->randomElement(['full-time', 'part-time', 'contract', 'freelance']),
                        'status' => $faker->randomElement(['draft', 'published', 'archived']),
                        'published_at' => $faker->dateTimeBetween('-3 months', 'now')
                    ]);

                    // Debug information
                    Log::info("Created job with ID: " . $job->id);

                    try {
                        // Attach 1-5 random languages
                        $selectedLanguages = $languages->random(rand(1, 5))->pluck('id')->toArray();
                        Log::info("Attaching languages: " . implode(', ', $selectedLanguages));
                        $job->languages()->attach($selectedLanguages);
                    } catch (Exception $e) {
                        Log::error("Error attaching languages to job {$job->id}: " . $e->getMessage());
                        throw $e;
                    }

                    try {
                        // Attach 1-5 random locations
                        $selectedLocations = $locations->random(rand(1, 5))->pluck('id')->toArray();
                        Log::info("Attaching locations: " . implode(', ', $selectedLocations));
                        $job->locations()->attach($selectedLocations);
                    } catch (Exception $e) {
                        Log::error("Error attaching locations to job {$job->id}: " . $e->getMessage());
                        throw $e;
                    }

                    try {
                        // Attach 1-5 random categories
                        $selectedCategories = $categories->random(rand(1, 5))->pluck('id')->toArray();
                        Log::info("Attaching categories: " . implode(', ', $selectedCategories));
                        $job->categories()->attach($selectedCategories);
                    } catch (Exception $e) {
                        Log::error("Error attaching categories to job {$job->id}: " . $e->getMessage());
                        throw $e;
                    }

                    // Add attribute values
                    foreach ($attributes as $attribute) {
                        try {
                            $value = match ($attribute->name) {
                                'experience_years' => $faker->numberBetween(0, 15),
                                'education_level' => $faker->randomElement(json_decode($attribute->options ?? '[]') ?: ['Bachelor\'s Degree']),
                                'work_environment' => $faker->randomElement(json_decode($attribute->options ?? '[]') ?: ['Office']),
                                'requires_travel' => $faker->boolean(20) ? '1' : '0',
                                'certification_required' => $faker->boolean(30) ? '1' : '0',
                                default => null
                            };

                            if ($value !== null) {
                                JobAttributeValue::create([
                                    'job_id' => $job->id,
                                    'attribute_id' => $attribute->id,
                                    'value' => (string) $value
                                ]);
                                Log::info("Added attribute {$attribute->name} with value {$value} to job {$job->id}");
                            }
                        } catch (Exception $e) {
                            Log::error("Error adding attribute {$attribute->name} to job {$job->id}: " . $e->getMessage());
                            throw $e;
                        }
                    }

                } catch (Exception $e) {
                    Log::error("Error creating job #{$i}: " . $e->getMessage());
                    throw $e;
                }
            }

            DB::commit();
            Log::info("Successfully seeded all jobs!");

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Fatal error in JobSeeder: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
}
