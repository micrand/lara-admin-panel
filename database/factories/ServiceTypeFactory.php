<?php

namespace Database\Factories;

use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<ServiceType>
 */
class ServiceTypeFactory extends Factory
{
    protected $model = ServiceType::class;

    public function definition(): array
    {
        $type = fake()->unique()->randomElement([
            'hosting',
            'domain',
            'email',
            'dns',
            'ssl',
        ]);

        return [
            'name' => ucfirst($type),
            'slug' => $type,
            'description' => fake()->sentence(),
            'category' => $type,
            'is_active' => true,
        ];
    }
}