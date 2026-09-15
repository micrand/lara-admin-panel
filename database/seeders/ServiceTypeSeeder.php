<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $serviceTypes = [
            [
                'name' => 'Hosting',
                'slug' => 'hosting',
                'description' => 'Web hosting service.',
                'category' => 'hosting',
                'is_active' => true,
            ],
            [
                'name' => 'Domain',
                'slug' => 'domain',
                'description' => 'Domain name management service.',
                'category' => 'domain',
                'is_active' => true,
            ],
            [
                'name' => 'Email',
                'slug' => 'email',
                'description' => 'Professional email service.',
                'category' => 'email',
                'is_active' => true,
            ],
            [
                'name' => 'DNS',
                'slug' => 'dns',
                'description' => 'DNS management service.',
                'category' => 'dns',
                'is_active' => true,
            ],
            [
                'name' => 'SSL',
                'slug' => 'ssl',
                'description' => 'SSL certificate service.',
                'category' => 'ssl',
                'is_active' => true,
            ],
        ];

        foreach ($serviceTypes as $serviceType) {
            ServiceType::updateOrCreate(
                ['slug' => $serviceType['slug']],
                $serviceType
            );
        }
    }
}