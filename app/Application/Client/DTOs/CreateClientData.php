<?php

namespace App\Application\Client\DTOs;

final readonly class CreateClientData
{
    public function __construct(
        public string $type,
        public string $name,
        public ?string $legalName = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $addressComplement = null,
        public ?string $postalCode = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $country = null,
        public ?array $metadata = null,
    ) {
    }
}