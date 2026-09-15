<?php

namespace App\Application\Dns\DTOs;

final readonly class CreateDnsRecordData
{
    public function __construct(
        public string $dnsZoneId,
        public string $type,
        public string $name,
        public string $value,
        public int $ttl = 3600,
        public ?int $priority = null,
        public ?int $weight = null,
        public ?int $port = null,
        public ?string $externalId = null,
    ) {
    }
}