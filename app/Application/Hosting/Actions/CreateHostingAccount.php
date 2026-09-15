<?php

namespace App\Application\Hosting\Actions;

use App\Application\Hosting\DTOs\CreateHostingAccountData;
use App\Models\HostingAccount;
use App\Models\ProvisioningOperation;
use App\Models\Server;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateHostingAccount
{
    public function execute(
        CreateHostingAccountData $data
    ): HostingAccount {
        return DB::transaction(function () use ($data): HostingAccount {
            /*
             * ---------------------------------------------------------
             * 1. Find the service
             * ---------------------------------------------------------
             */

            $service = Service::query()
                ->with('serviceType')
                ->find($data->serviceId);

            if ($service === null) {
                throw (new ModelNotFoundException())
                    ->setModel(Service::class, [$data->serviceId]);
            }

            /*
             * ---------------------------------------------------------
             * 2. Verify service type
             * ---------------------------------------------------------
             */

            if ($service->serviceType?->slug !== 'hosting') {
                throw new \DomainException(
                    'The service must be a hosting service.'
                );
            }

            /*
             * ---------------------------------------------------------
             * 3. Verify service status
             * ---------------------------------------------------------
             */

            if (! in_array($service->status, [
                'pending',
                'provisioning',
            ], true)) {
                throw new \DomainException(
                    'The hosting service cannot be provisioned in its current status.'
                );
            }

            /*
             * ---------------------------------------------------------
             * 4. Find the server
             * ---------------------------------------------------------
             */

            $server = Server::query()->find($data->serverId);

            if ($server === null) {
                throw (new ModelNotFoundException())
                    ->setModel(Server::class, [$data->serverId]);
            }

            /*
             * ---------------------------------------------------------
             * 5. Verify server status
             * ---------------------------------------------------------
             */

            if ($server->status !== 'active') {
                throw new \DomainException(
                    'The server is not available for provisioning.'
                );
            }

            /*
             * ---------------------------------------------------------
             * 6. Prevent duplicate hosting account
             * ---------------------------------------------------------
             */

            $existingAccount = HostingAccount::query()
                ->where('service_id', $service->id)
                ->whereNull('deleted_at')
                ->first();

            if ($existingAccount !== null) {
                throw new \DomainException(
                    'A hosting account already exists for this service.'
                );
            }

            /*
             * ---------------------------------------------------------
             * 7. Create hosting account
             * ---------------------------------------------------------
             */

            $hostingAccount = HostingAccount::create([
                'service_id' => $service->id,
                'server_id' => $server->id,
                'provider_account_id' => $data->providerAccountId,
                'username' => $data->username,
                'primary_domain' => $data->primaryDomain,
                'external_id' => $data->externalId,
                'status' => 'pending',
                'last_synced_at' => null,
                'metadata' => $data->metadata,
            ]);

            /*
             * ---------------------------------------------------------
             * 8. Create provisioning operation
             * ---------------------------------------------------------
             */

            ProvisioningOperation::create([
                'client_id' => $service->client_id,
                'server_id' => $server->id,
                'service_id' => $service->id,
                'operation' => 'create',
                'resource_type' => HostingAccount::class,
                'resource_id' => $hostingAccount->id,
                'status' => 'pending',
                'attempts' => 0,
                'idempotency_key' => $this->generateIdempotencyKey(
                    $service->id
                ),
                'request' => [
                    'username' => $data->username,
                    'primary_domain' => $data->primaryDomain,
                    'server_id' => $server->id,
                ],
                'response' => null,
                'errors' => null,
                'started_at' => null,
                'completed_at' => null,
            ]);

            return $hostingAccount;
        });
    }

    private function generateIdempotencyKey(string $serviceId): string
    {
        return 'hosting:create:' . $serviceId;
    }
}