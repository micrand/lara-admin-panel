<?php

namespace Database\Factories;

use App\Models\HostingAccount;
use App\Models\Server;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<HostingAccount>
 */
class HostingAccountFactory extends Factory
{
    protected $model = HostingAccount::class;

    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'server_id' => Server::factory(),
            'provider_account_id' => 'CP-' . fake()->unique()->numerify('########'),
            'username' => fake()->unique()->userName(),
            'primary_domain' => fake()->unique()->domainName(),
            'external_id' => 'HOST-' . fake()->unique()->numerify('########'),
            'status' => 'active',
            'last_synced_at' => now(),
            'metadata' => [
                'source' => 'factory',
            ],
        ];
    }

    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
            'last_synced_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state([
            'status' => 'suspended',
        ]);
    }
}