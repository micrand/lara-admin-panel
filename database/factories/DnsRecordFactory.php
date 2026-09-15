<?php

namespace Database\Factories;

use App\Models\DnsRecord;
use App\Models\DnsZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<DnsRecord>
 */
class DnsRecordFactory extends Factory
{
    protected $model = DnsRecord::class;

    public function definition(): array
    {
        return [
            'dns_zone_id' => DnsZone::factory(),
            'type' => 'A',
            'name' => '@',
            'value' => fake()->ipv4(),
            'ttl' => 3600,
            'priority' => null,
            'weight' => null,
            'port' => null,
            'status' => 'active',
            'external_id' => 'DNS-' . fake()->unique()->numerify('########'),
        ];
    }

    public function a(): static
    {
        return $this->state([
            'type' => 'A',
            'value' => fake()->ipv4(),
        ]);
    }

    public function aaaa(): static
    {
        return $this->state([
            'type' => 'AAAA',
            'value' => fake()->ipv6(),
        ]);
    }

    public function cname(): static
    {
        return $this->state([
            'type' => 'CNAME',
            'name' => 'www',
            'value' => fake()->domainName(),
        ]);
    }

    public function mx(): static
    {
        return $this->state([
            'type' => 'MX',
            'name' => '@',
            'value' => 'mail.' . fake()->domainName(),
            'priority' => fake()->numberBetween(1, 50),
        ]);
    }

    public function txt(): static
    {
        return $this->state([
            'type' => 'TXT',
            'name' => '@',
            'value' => 'v=spf1 include:example.com ~all',
        ]);
    }
}