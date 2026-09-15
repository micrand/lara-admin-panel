<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Domain;
use App\Models\HostingAccount;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Domain>
 */
class DomainFactory extends Factory
{
    protected $model = Domain::class;

    public function definition(): array
    {
        $domain = fake()->unique()->domainName();

        return [
            'client_id' => Client::factory(),
            'service_id' => Service::factory(),
            'hosting_account_id' => HostingAccount::factory(),
            'parent_domain_id' => null,
            'name' => $domain,
            'fqdn' => $domain,
            'type' => 'primary',
            'status' => 'active',
            'expires_at' => now()->addYear(),
            'external_id' => 'DOM-' . fake()->unique()->numerify('########'),
            'last_synced_at' => now(),
            'metadata' => [
                'source' => 'factory',
            ],
        ];
    }

    public function subdomain(): static
    {
        return $this->state([
            'type' => 'subdomain',
        ]);
    }

    public function alias(): static
    {
        return $this->state([
            'type' => 'alias',
        ]);
    }

    public function expired(): static
    {
        return $this->state([
            'status' => 'expired',
            'expires_at' => now()->subMonth(),
        ]);
    }
}