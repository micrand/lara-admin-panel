<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Server>
 */
class ServerFactory extends Factory
{
    protected $model = Server::class;

    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'name' => fake()->unique()->word() . '-server',
            'hostname' => fake()->unique()->domainName(),
            'ip' => fake()->ipv4(),
            'port' => 2087,
            'status' => 'active',
            'environment' => 'production',
            'api_configuration' => [
                'verify_ssl' => true,
            ],
            'last_checked_at' => now(),
        ];
    }

    public function staging(): static
    {
        return $this->state([
            'environment' => 'staging',
        ]);
    }

    public function testing(): static
    {
        return $this->state([
            'environment' => 'testing',
        ]);
    }
}