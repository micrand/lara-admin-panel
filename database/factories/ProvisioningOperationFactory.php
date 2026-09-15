<?php

namespace Database\Factories;

use App\Models\ProvisioningOperation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProvisioningOperation>
 */
class ProvisioningOperationFactory extends Factory
{
    protected $model = ProvisioningOperation::class;

    public function definition(): array
    {
        return [
            'operation' => 'create',
            'resource_type' => 'HostingAccount',
            'resource_id' => Str::uuid()->toString(),
            'status' => 'pending',
            'attempts' => 0,
            'idempotency_key' => 'test:' . Str::uuid(),
            'request' => [
                'source' => 'test',
            ],
            'response' => null,
            'errors' => null,
            'started_at' => null,
            'completed_at' => null,
        ];
    }
}