<?php

namespace App\Application\Hosting\DTOs;

final readonly class CreateHostingServiceData
{
    public function __construct(
        public string $clientId,
        public string $name,
        public ?string $startsAt = null,
        public ?string $expiresAt = null,
        public ?array $metadata = null,
    ) {
    }
}