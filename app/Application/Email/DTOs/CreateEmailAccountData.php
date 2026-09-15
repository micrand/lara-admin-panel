<?php

namespace App\Application\Email\DTOs;

final readonly class CreateEmailAccountData
{
    public function __construct(
        public string $clientId,
        public string $serviceId,
        public string $domainId,
        public string $hostingAccountId,
        public string $localPart,
        public int $quota = 5120,
        public ?string $externalId = null,
        public ?array $metadata = null,
    ) {
    }
}