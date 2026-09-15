<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\EmailForwarder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<EmailForwarder>
 */
class EmailForwarderFactory extends Factory
{
    protected $model = EmailForwarder::class;

    public function definition(): array
    {
        return [
            'domain_id' => Domain::factory(),
            'source' => fake()->unique()->userName(),
            'destination' => fake()->safeEmail(),
            'status' => 'active',
            'external_id' => 'FWD-' . fake()->unique()->numerify('########'),
        ];
    }
}