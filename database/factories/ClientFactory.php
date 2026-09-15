<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $type = fake()->randomElement([
            'individual',
            'company',
            'organization',
        ]);

        return [
            'type' => $type,
            'name' => fake()->company(),
            'legal_name' => fake()->optional()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address_line1' => fake()->streetAddress(),
            'address_line2' => fake()->optional()->secondaryAddress(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'state' => fake()->optional()->state(),
            'country_code' => 'FR',
            'status' => 'active',
            'metadata' => [
                'source' => 'factory',
            ],
        ];
    }
}