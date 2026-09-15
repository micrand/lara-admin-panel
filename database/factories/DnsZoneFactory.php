<?php

namespace Database\Factories;

use App\Models\DnsZone;
use App\Models\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<DnsZone>
 */
class DnsZoneFactory extends Factory
{
    protected $model = DnsZone::class;

    public function definition(): array
    {
        return [
            'domain_id' => Domain::factory(),
            'name' => fake()->unique()->domainName(),
            'status' => 'active',
            'serial' => (int) now()->format('Ymd') . '01',
            'provider_zone_id' => 'ZONE-' . fake()->unique()->numerify('########'),
            'last_synced_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
            'last_synced_at' => null,
        ]);
    }
}