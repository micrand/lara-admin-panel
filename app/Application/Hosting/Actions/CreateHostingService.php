<?php

namespace App\Application\Hosting\Actions;

use App\Application\Hosting\DTOs\CreateHostingServiceData;
use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateHostingService
{
    public function execute(CreateHostingServiceData $data): Service
    {
        return DB::transaction(function () use ($data): Service {
            $client = Client::query()->find($data->clientId);

            if ($client === null) {
                throw (new ModelNotFoundException())
                    ->setModel(Client::class, [$data->clientId]);
            }

            $serviceType = ServiceType::query()
                ->where('slug', 'hosting')
                ->where('is_active', true)
                ->first();

            if ($serviceType === null) {
                throw new ModelNotFoundException(
                    'Active hosting service type not found.'
                );
            }

            return Service::create([
                'client_id' => $client->id,
                'service_type_id' => $serviceType->id,
                'name' => $data->name,
                'reference' => $this->generateReference(),
                'status' => 'pending',
                'starts_at' => $data->startsAt,
                'expires_at' => $data->expiresAt,
                'metadata' => $data->metadata,
            ]);
        });
    }

    private function generateReference(): string
    {
        return 'HOST-' . strtoupper(
            Str::random(10)
        );
    }
}