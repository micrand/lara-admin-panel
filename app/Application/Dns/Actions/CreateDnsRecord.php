<?php

namespace App\Application\Dns\Actions;

use App\Application\Dns\DTOs\CreateDnsRecordData;
use App\Models\DnsRecord;
use App\Models\DnsZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreateDnsRecord
{
    private const ALLOWED_TYPES = [
        'A',
        'AAAA',
        'CNAME',
        'MX',
        'TXT',
        'NS',
        'SRV',
        'CAA',
    ];

    public function execute(CreateDnsRecordData $data): DnsRecord
    {
        return DB::transaction(function () use ($data): DnsRecord {
            $zone = DnsZone::query()
                ->whereKey($data->dnsZoneId)
                ->first();

            if (! $zone) {
                throw ValidationException::withMessages([
                    'dns_zone_id' => 'DNS zone not found.',
                ]);
            }

            if (! in_array($zone->status, [
                'pending',
                'active',
            ], true)) {
                throw ValidationException::withMessages([
                    'dns_zone_id' => 'The DNS zone is not available for record creation.',
                ]);
            }

            $type = strtoupper(trim($data->type));

            if (! in_array($type, self::ALLOWED_TYPES, true)) {
                throw ValidationException::withMessages([
                    'type' => 'Unsupported DNS record type.',
                ]);
            }

            if ($data->ttl < 60 || $data->ttl > 86400) {
                throw ValidationException::withMessages([
                    'ttl' => 'TTL must be between 60 and 86400 seconds.',
                ]);
            }

            $name = strtolower(trim($data->name));

            if ($name === '') {
                throw ValidationException::withMessages([
                    'name' => 'DNS record name is required.',
                ]);
            }

            $value = trim($data->value);

            if ($value === '') {
                throw ValidationException::withMessages([
                    'value' => 'DNS record value is required.',
                ]);
            }

            $this->validateTypeSpecificFields(
                $type,
                $data
            );

            return DnsRecord::query()->create([
                'dns_zone_id' => $zone->id,
                'type' => $type,
                'name' => $name,
                'value' => $value,
                'ttl' => $data->ttl,
                'priority' => $data->priority,
                'weight' => $data->weight,
                'port' => $data->port,
                'status' => 'pending',
                'external_id' => $data->externalId,
            ]);
        });
    }

    private function validateTypeSpecificFields(
        string $type,
        CreateDnsRecordData $data
    ): void {
        if ($type === 'MX' && $data->priority === null) {
            throw ValidationException::withMessages([
                'priority' => 'Priority is required for MX records.',
            ]);
        }

        if ($type === 'SRV') {
            if ($data->priority === null) {
                throw ValidationException::withMessages([
                    'priority' => 'Priority is required for SRV records.',
                ]);
            }

            if ($data->weight === null) {
                throw ValidationException::withMessages([
                    'weight' => 'Weight is required for SRV records.',
                ]);
            }

            if ($data->port === null) {
                throw ValidationException::withMessages([
                    'port' => 'Port is required for SRV records.',
                ]);
            }
        }

        if ($data->priority !== null && (
            $data->priority < 0 ||
            $data->priority > 65535
        )) {
            throw ValidationException::withMessages([
                'priority' => 'Priority must be between 0 and 65535.',
            ]);
        }

        if ($data->weight !== null && (
            $data->weight < 0 ||
            $data->weight > 65535
        )) {
            throw ValidationException::withMessages([
                'weight' => 'Weight must be between 0 and 65535.',
            ]);
        }

        if ($data->port !== null && (
            $data->port < 1 ||
            $data->port > 65535
        )) {
            throw ValidationException::withMessages([
                'port' => 'Port must be between 1 and 65535.',
            ]);
        }
    }
}