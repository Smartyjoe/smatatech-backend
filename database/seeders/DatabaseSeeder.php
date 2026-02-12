<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SiteSettingsSeeder::class,
            ChatbotSeeder::class,
            ChatbotTrainingSeeder::class,
            EmailSettingsSeeder::class,
            EmailTemplateSeeder::class,
            BrandsSeeder::class,
            ServicesSeeder::class,
            CaseStudiesSeeder::class,
            TestimonialsSeeder::class,
            BlogSeeder::class,
            ContactMessagesSeeder::class,
        ]);

        $this->command->info('Database seeded successfully!');
    }
}
