<?php

namespace Database\Factories;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Provider>
 */
class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'type' => fake()->randomElement([
                'cpanel',
                'plesk',
                'other',
            ]),
            'status' => 'active',
            'capabilities' => [
                'hosting' => true,
                'domains' => true,
                'dns' => true,
                'email' => true,
            ],
            'configuration' => [],
        ];
    }

    public function cpanel(): static
    {
        return $this->state([
            'name' => 'cPanel',
            'slug' => 'cpanel',
            'type' => 'cpanel',
        ]);
    }

    public function plesk(): static
    {
        return $this->state([
            'name' => 'Plesk',
            'slug' => 'plesk',
            'type' => 'plesk',
        ]);
    }
}