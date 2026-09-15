<?php

namespace App\Application\Client\Actions;

use App\Application\Client\DTOs\CreateClientData;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

final class CreateClient
{
    public function execute(CreateClientData $data): Client
    {
        return DB::transaction(function () use ($data): Client {
            return Client::create([
                'type' => $data->type,
                'name' => $data->name,
                'legal_name' => $data->legalName,
                'email' => $data->email,
                'phone' => $data->phone,
                'address' => $data->address,
                'address_complement' => $data->addressComplement,
                'postal_code' => $data->postalCode,
                'city' => $data->city,
                'state' => $data->state,
                'country' => $data->country,
                'status' => 'active',
                'metadata' => $data->metadata,
            ]);
        });
    }
}