<?php

namespace App\Application\Dns\Actions;

use App\Application\Dns\DTOs\CreateDnsZoneData;
use App\Models\DnsZone;
use App\Models\Domain;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateDnsZone
{
    public function execute(CreateDnsZoneData $data): DnsZone
    {
        return DB::transaction(function () use ($data): DnsZone {
            $domain = Domain::query()
                ->whereKey($data->domainId)
                ->whereNull('deleted_at')
                ->first();

            if (! $domain) {
                throw ValidationException::withMessages([
                    'domain_id' => 'Domain not found.',
                ]);
            }

            if (! in_array($domain->status, [
                'pending',
                'provisioning',
                'active',
            ], true)) {
                throw ValidationException::withMessages([
                    'domain_id' => 'The domain is not available for DNS zone creation.',
                ]);
            }

            if (DnsZone::query()
                ->where('domain_id', $domain->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'domain_id' => 'A DNS zone already exists for this domain.',
                ]);
            }

            return DnsZone::query()->create([
                'domain_id' => $domain->id,
                'name' => strtolower($domain->fqdn),
                'status' => 'pending',
                'provider_zone_id' => $data->providerZoneId,
            ]);
        });
    }
}