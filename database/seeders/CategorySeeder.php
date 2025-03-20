<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Backend Development',
            'Frontend Development',
            'Full Stack Development',
            'Mobile Development',
            'DevOps',
            'Data Science',
            'Machine Learning',
            'Cloud Computing',
            'Security',
            'UI/UX Design',
            'Product Management',
            'QA Engineering',
            'Database Administration',
            'Systems Architecture',
            'Blockchain Development'
        ];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}
