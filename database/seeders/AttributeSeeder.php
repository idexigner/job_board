<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run()
    {
        $attributes = [
            [
                'name' => 'experience_years',
                'type' => 'number'
            ],
            [
                'name' => 'education_level',
                'type' => 'select',
                'options' => json_encode([
                    'High School',
                    'Bachelor\'s Degree',
                    'Master\'s Degree',
                    'PhD',
                    'Other'
                ])
            ],
            [
                'name' => 'work_environment',
                'type' => 'select',
                'options' => json_encode([
                    'Office',
                    'Hybrid',
                    'Remote'
                ])
            ],
            [
                'name' => 'requires_travel',
                'type' => 'boolean'
            ],
            [
                'name' => 'certification_required',
                'type' => 'boolean'
            ]
        ];

        foreach ($attributes as $attribute) {
            Attribute::create($attribute);
        }
    }
}
