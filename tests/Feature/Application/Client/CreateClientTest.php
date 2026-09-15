<?php

namespace Tests\Feature\Application\Client;

use App\Application\Client\Actions\CreateClient;
use App\Application\Client\DTOs\CreateClientData;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_client(): void
    {
        $data = new CreateClientData(
            type: 'company',
            name: 'ACME Corporation',
            legalName: 'ACME Corporation SAS',
            email: 'contact@acme.test',
            phone: '+33100000000',
            address: '10 Rue de Paris',
            postalCode: '75001',
            city: 'Paris',
            country: 'FR',
        );

        $client = app(CreateClient::class)->execute($data);

        $this->assertInstanceOf(Client::class, $client);

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'ACME Corporation',
            'legal_name' => 'ACME Corporation SAS',
            'email' => 'contact@acme.test',
            'type' => 'company',
            'status' => 'active',
        ]);
    }

    public function test_it_returns_the_created_client(): void
    {
        $data = new CreateClientData(
            type: 'individual',
            name: 'John Doe',
            email: 'john.doe@example.test',
            country: 'FR',
        );

        $client = app(CreateClient::class)->execute($data);

        $this->assertSame('John Doe', $client->name);
        $this->assertSame('individual', $client->type);
        $this->assertSame('john.doe@example.test', $client->email);
    }
}