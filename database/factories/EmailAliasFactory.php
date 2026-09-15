<?php

namespace Database\Factories;

use App\Models\EmailAccount;
use App\Models\EmailAlias;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<EmailAlias>
 */
class EmailAliasFactory extends Factory
{
    protected $model = EmailAlias::class;

    public function definition(): array
    {
        return [
            'email_account_id' => EmailAccount::factory(),
            'alias' => fake()->unique()->safeEmail(),
            'status' => 'active',
        ];
    }
}