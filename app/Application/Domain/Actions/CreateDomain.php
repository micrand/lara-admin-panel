<?php

namespace App\Application\Domain\Actions;

use App\Application\Domain\DTOs\CreateDomainData;
use App\Models\Client;
use App\Models\Domain;
use App\Models\HostingAccount;
use App\Models\Service;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class CreateDomain
{
    public function execute(CreateDomainData $data): Domain
    {
        return DB::transaction(function () use ($data): Domain {
            /*
             * ---------------------------------------------------------
             * 1. Client
             * ---------------------------------------------------------
             */

            $client = Client::query()->find($data->clientId);

            if ($client === null) {
                throw (new ModelNotFoundException())
                    ->setModel(Client::class, [$data->clientId]);
            }

            /*
             * ---------------------------------------------------------
             * 2. Domain service
             * ---------------------------------------------------------
             */

            $service = Service::query()
                ->with('serviceType')
                ->where('id', $data->serviceId)
                ->where('client_id', $client->id)
                ->first();

            if ($service === null) {
                throw (new ModelNotFoundException())
                    ->setModel(Service::class, [$data->serviceId]);
            }

            if ($service->serviceType?->slug !== 'domain') {
                throw new \DomainException(
                    'The service must be a domain service.'
                );
            }

            if (! in_array($service->status, [
                'pending',
                'provisioning',
                'active',
            ], true)) {
                throw new \DomainException(
                    'The domain service cannot be used in its current status.'
                );
            }

            /*
             * ---------------------------------------------------------
             * 3. Domain type
             * ---------------------------------------------------------
             */

            if (! in_array($data->type, [
                'primary',
                'subdomain',
                'alias',
            ], true)) {
                throw new \DomainException(
                    'Invalid domain type.'
                );
            }

            /*
             * ---------------------------------------------------------
             * 4. Parent domain for subdomains
             * ---------------------------------------------------------
             */

            $parentDomain = null;

            if ($data->type === 'subdomain') {
                if ($data->parentDomainId === null) {
                    throw new \DomainException(
                        'A subdomain must have a parent domain.'
                    );
                }

                $parentDomain = Domain::query()
                    ->where('id', $data->parentDomainId)
                    ->where('client_id', $client->id)
                    ->whereNull('deleted_at')
                    ->first();

                if ($parentDomain === null) {
                    throw (new ModelNotFoundException())
                        ->setModel(Domain::class, [$data->parentDomainId]);
                }

                if ($parentDomain->type !== 'primary') {
                    throw new \DomainException(
                        'A subdomain parent must be a primary domain.'
                    );
                }
            }

            /*
             * ---------------------------------------------------------
             * 5. Hosting account
             * ---------------------------------------------------------
             */

            $hostingAccount = null;

            if ($data->hostingAccountId !== null) {
                $hostingAccount = HostingAccount::query()
                    ->where('id', $data->hostingAccountId)
                    ->whereNull('deleted_at')
                    ->whereHas('service', function ($query) use ($client): void {
                        $query->where('client_id', $client->id);
                    })
                    ->first();

                if ($hostingAccount === null) {
                    throw (new ModelNotFoundException())
                        ->setModel(
                            HostingAccount::class,
                            [$data->hostingAccountId]
                        );
                }
            }

            /*
             * ---------------------------------------------------------
             * 6. Resolve FQDN
             * ---------------------------------------------------------
             */

            $fqdn = $data->fqdn;

            if ($fqdn === null) {
                if ($data->type === 'subdomain') {
                    $fqdn = $data->name . '.' . $parentDomain->fqdn;
                } else {
                    $fqdn = $data->name;
                }
            }

            $fqdn = strtolower(trim($fqdn));

            /*
             * ---------------------------------------------------------
             * 7. Prevent duplicate active domain
             * ---------------------------------------------------------
             */

            $existingDomain = Domain::query()
                ->whereRaw('LOWER(fqdn) = ?', [$fqdn])
                ->whereNull('deleted_at')
                ->first();

            if ($existingDomain !== null) {
                throw new \DomainException(
                    'An active domain with this FQDN already exists.'
                );
            }

            /*
             * ---------------------------------------------------------
             * 8. Create domain
             * ---------------------------------------------------------
             */

            return Domain::create([
                'client_id' => $client->id,
                'service_id' => $service->id,
                'hosting_account_id' => $hostingAccount?->id,
                'parent_domain_id' => $parentDomain?->id,
                'name' => $data->name,
                'fqdn' => $fqdn,
                'type' => $data->type,
                'status' => 'pending',
                'expires_at' => $data->expiresAt,
                'external_id' => $data->externalId,
                'last_synced_at' => null,
                'metadata' => $data->metadata,
            ]);
        });
    }
}