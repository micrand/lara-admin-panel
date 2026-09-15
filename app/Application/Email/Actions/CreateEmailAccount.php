<?php

namespace App\Application\Email\Actions;

use App\Application\Email\DTOs\CreateEmailAccountData;
use App\Models\Client;
use App\Models\Domain;
use App\Models\EmailAccount;
use App\Models\HostingAccount;
use App\Models\Service;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class CreateEmailAccount
{
    public function execute(
        CreateEmailAccountData $data
    ): EmailAccount {
        return DB::transaction(function () use ($data): EmailAccount {
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
             * 2. Email service
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

            if ($service->serviceType?->slug !== 'email') {
                throw new \DomainException(
                    'The service must be an email service.'
                );
            }

            if (! in_array($service->status, [
                'pending',
                'provisioning',
                'active',
            ], true)) {
                throw new \DomainException(
                    'The email service cannot be used in its current status.'
                );
            }

            /*
             * ---------------------------------------------------------
             * 3. Domain
             * ---------------------------------------------------------
             */

            $domain = Domain::query()
                ->where('id', $data->domainId)
                ->where('client_id', $client->id)
                ->whereNull('deleted_at')
                ->first();

            if ($domain === null) {
                throw (new ModelNotFoundException())
                    ->setModel(Domain::class, [$data->domainId]);
            }

            if (! in_array($domain->status, [
                'active',
                'provisioning',
            ], true)) {
                throw new \DomainException(
                    'The domain is not available for email provisioning.'
                );
            }

            /*
             * ---------------------------------------------------------
             * 4. Hosting account
             * ---------------------------------------------------------
             */

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

            if (! in_array($hostingAccount->status, [
                'active',
                'provisioning',
            ], true)) {
                throw new \DomainException(
                    'The hosting account is not available for email provisioning.'
                );
            }

            /*
             * ---------------------------------------------------------
             * 5. Validate local part
             * ---------------------------------------------------------
             */

            $localPart = strtolower(trim($data->localPart));

            if ($localPart === '') {
                throw new \DomainException(
                    'The email local part cannot be empty.'
                );
            }

            if (! preg_match(
                '/^[a-z0-9.!#$%&\'*+\/=?^_`{|}~-]+$/i',
                $localPart
            )) {
                throw new \DomainException(
                    'The email local part contains invalid characters.'
                );
            }

            /*
             * ---------------------------------------------------------
             * 6. Build email address
             * ---------------------------------------------------------
             */

            $email = $localPart . '@' . strtolower($domain->fqdn);

            /*
             * ---------------------------------------------------------
             * 7. Prevent duplicate email account
             * ---------------------------------------------------------
             */

            $existingAccount = EmailAccount::query()
                ->where('domain_id', $domain->id)
                ->whereRaw(
                    'LOWER(local_part) = ?',
                    [$localPart]
                )
                ->whereNull('deleted_at')
                ->first();

            if ($existingAccount !== null) {
                throw new \DomainException(
                    'An email account with this local part already exists for the domain.'
                );
            }

            /*
             * ---------------------------------------------------------
             * 8. Create email account
             * ---------------------------------------------------------
             */

            return EmailAccount::create([
                'client_id' => $client->id,
                'service_id' => $service->id,
                'domain_id' => $domain->id,
                'hosting_account_id' => $hostingAccount->id,
                'local_part' => $localPart,
                'email' => $email,
                'quota' => $data->quota,
                'status' => 'pending',
                'external_id' => $data->externalId,
                'last_synced_at' => null,
                'metadata' => $data->metadata,
            ]);
        });
    }
}