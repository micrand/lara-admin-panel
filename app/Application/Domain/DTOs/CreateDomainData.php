<?php

namespace App\Application\Domain\DTOs;

final readonly class CreateDomainData
{
    public function __construct(
        public string $clientId,
        public string $serviceId,
        public string $name,
        public ?string $fqdn = null,
        public string $type = 'primary',
        public ?string $hostingAccountId = null,
        public ?string $parentDomainId = null,
        public ?string $expiresAt = null,
        public ?string $externalId = null,
        public ?array $metadata = null,
    ) {
    }
}