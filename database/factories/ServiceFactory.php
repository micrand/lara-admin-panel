<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'service_type_id' => ServiceType::query()->inRandomOrder()->value('id'),
            'name' => fake()->words(3, true),
            'reference' => 'SRV-' . strtoupper(fake()->unique()->bothify('####??')),
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
            'metadata' => [
                'source' => 'factory',
            ],
        ];
    }

    public function hosting(): static
    {
        return $this->state([
            'service_type_id' => ServiceType::where('slug', 'hosting')->value('id'),
        ]);
    }

    public function domain(): static
    {
        return $this->state([
            'service_type_id' => ServiceType::where('slug', 'domain')->value('id'),
        ]);
    }

    public function email(): static
    {
        return $this->state([
            'service_type_id' => ServiceType::where('slug', 'email')->value('id'),
        ]);
    }

    public function dns(): static
    {
        return $this->state([
            'service_type_id' => ServiceType::where('slug', 'dns')->value('id'),
        ]);
    }

    public function ssl(): static
    {
        return $this->state([
            'service_type_id' => ServiceType::where('slug', 'ssl')->value('id'),
        ]);
    }
}