<?php

namespace App\Application\Dns\DTOs;

final readonly class CreateDnsZoneData
{
    public function __construct(
        public string $domainId,
        public ?string $providerZoneId = null,
    ) {
    }
}