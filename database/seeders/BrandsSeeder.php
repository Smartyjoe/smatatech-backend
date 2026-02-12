<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'TechCorp',
                'logo' => '/images/brands/techcorp.svg',
                'website' => 'https://techcorp.com',
                'status' => 'active',
                'order' => 1,
            ],
            [
                'name' => 'InnovateLabs',
                'logo' => '/images/brands/innovatelabs.svg',
                'website' => 'https://innovatelabs.io',
                'status' => 'active',
                'order' => 2,
            ],
            [
                'name' => 'FutureWorks',
                'logo' => '/images/brands/futureworks.svg',
                'website' => 'https://futureworks.dev',
                'status' => 'active',
                'order' => 3,
            ],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['name' => $brand['name']],
                $brand
            );
        }
    }
}
