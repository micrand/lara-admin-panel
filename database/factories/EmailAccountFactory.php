<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Domain;
use App\Models\EmailAccount;
use App\Models\HostingAccount;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<EmailAccount>
 */
class EmailAccountFactory extends Factory
{
    protected $model = EmailAccount::class;

    public function definition(): array
    {
        $localPart = fake()->unique()->userName();

        return [
            'client_id' => Client::factory(),
            'service_id' => Service::factory(),
            'domain_id' => Domain::factory(),
            'hosting_account_id' => HostingAccount::factory(),
            'local_part' => $localPart,
            'email' => $localPart . '@example.test',
            'quota' => 5120,
            'status' => 'active',
            'external_id' => 'MAIL-' . fake()->unique()->numerify('########'),
            'last_synced_at' => now(),
            'metadata' => [
                'source' => 'factory',
            ],
        ];
    }

    public function suspended(): static
    {
        return $this->state([
            'status' => 'suspended',
        ]);
    }

    public function unlimited(): static
    {
        return $this->state([
            'quota' => 0,
        ]);
    }
}