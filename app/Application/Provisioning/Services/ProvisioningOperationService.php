<?php

namespace App\Application\Provisioning\Services;

use App\Models\ProvisioningOperation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ProvisioningOperationService
{
    public function start(ProvisioningOperation $operation): ProvisioningOperation
    {
        if ($operation->status !== 'pending') {
            throw ValidationException::withMessages([
                'operation' => 'Only pending provisioning operations can be started.',
            ]);
        }

        return DB::transaction(function () use ($operation): ProvisioningOperation {
            $operation->update([
                'status' => 'processing',
                'attempts' => $operation->attempts + 1,
                'started_at' => now(),
            ]);

            return $operation->refresh();
        });
    }

    public function complete(
        ProvisioningOperation $operation,
        ?array $response = null
    ): ProvisioningOperation {
        if ($operation->status !== 'processing') {
            throw ValidationException::withMessages([
                'operation' => 'Only processing provisioning operations can be completed.',
            ]);
        }

        $operation->update([
            'status' => 'completed',
            'response' => $response,
            'completed_at' => now(),
        ]);

        return $operation->refresh();
    }

    public function fail(
        ProvisioningOperation $operation,
        array $errors = []
    ): ProvisioningOperation {
        if ($operation->status !== 'processing') {
            throw ValidationException::withMessages([
                'operation' => 'Only processing provisioning operations can be failed.',
            ]);
        }

        $operation->update([
            'status' => 'failed',
            'errors' => $errors,
            'completed_at' => now(),
        ]);

        return $operation->refresh();
    }

    public function cancel(
        ProvisioningOperation $operation
    ): ProvisioningOperation {
        if (in_array($operation->status, [
            'completed',
            'cancelled',
        ], true)) {
            throw ValidationException::withMessages([
                'operation' => 'This provisioning operation cannot be cancelled.',
            ]);
        }

        $operation->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);

        return $operation->refresh();
    }
}