<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run()
    {
        $languages = [
            'PHP', 'JavaScript', 'Python', 'Java', 'C#',
            'Ruby', 'Go', 'Swift', 'Kotlin', 'TypeScript',
            'Rust', 'C++', 'Scala', 'R', 'Dart'
        ];

        foreach ($languages as $language) {
            Language::create(['name' => $language]);
        }
    }
}
