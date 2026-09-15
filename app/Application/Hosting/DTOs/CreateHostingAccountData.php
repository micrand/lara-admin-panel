<?php

namespace App\Application\Hosting\DTOs;

final readonly class CreateHostingAccountData
{
    public function __construct(
        public string $serviceId,
        public string $serverId,
        public string $username,
        public string $primaryDomain,
        public ?string $providerAccountId = null,
        public ?string $externalId = null,
        public ?array $metadata = null,
    ) {
    }
}