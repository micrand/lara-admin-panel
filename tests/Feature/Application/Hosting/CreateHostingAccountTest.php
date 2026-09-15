<?php

namespace Tests\Feature\Application\Hosting;

use App\Application\Hosting\Actions\CreateHostingAccount;
use App\Application\Hosting\DTOs\CreateHostingAccountData;
use App\Models\Client;
use App\Models\HostingAccount;
use App\Models\Provider;
use App\Models\ProvisioningOperation;
use App\Models\Server;
use App\Models\Service;
use App\Models\ServiceType;
use Database\Seeders\ServiceTypeSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

class CreateHostingAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ServiceTypeSeeder::class);
    }

    public function test_it_creates_a_pending_hosting_account(): void
    {
        $client = Client::factory()->create();

        $hostingType = ServiceType::where(
            'slug',
            'hosting'
        )->firstOrFail();

        $service = Service::factory()
            ->for($client)
            ->state([
                'service_type_id' => $hostingType->id,
                'status' => 'pending',
            ])
            ->create();

        $provider = Provider::factory()
            ->cpanel()
            ->create();

        $server = Server::factory()
            ->for($provider)
            ->create([
                'status' => 'active',
            ]);

        $data = new CreateHostingAccountData(
            serviceId: $service->id,
            serverId: $server->id,
            username: 'acmetest',
            primaryDomain: 'acme.test',
        );

        $hostingAccount = app(CreateHostingAccount::class)
            ->execute($data);

        $this->assertInstanceOf(
            HostingAccount::class,
            $hostingAccount
        );

        $this->assertSame(
            $service->id,
            $hostingAccount->service_id
        );

        $this->assertSame(
            $server->id,
            $hostingAccount->server_id
        );

        $this->assertSame(
            'acmetest',
            $hostingAccount->username
        );

        $this->assertSame(
            'acme.test',
            $hostingAccount->primary_domain
        );

        $this->assertSame(
            'pending',
            $hostingAccount->status
        );
    }

    public function test_it_creates_a_pending_provisioning_operation(): void
    {
        $client = Client::factory()->create();

        $hostingType = ServiceType::where(
            'slug',
            'hosting'
        )->firstOrFail();

        $service = Service::factory()
            ->for($client)
            ->state([
                'service_type_id' => $hostingType->id,
                'status' => 'pending',
            ])
            ->create();

        $provider = Provider::factory()
            ->cpanel()
            ->create();

        $server = Server::factory()
            ->for($provider)
            ->create([
                'status' => 'active',
            ]);

        $data = new CreateHostingAccountData(
            serviceId: $service->id,
            serverId: $server->id,
            username: 'acmetest',
            primaryDomain: 'acme.test',
        );

        $hostingAccount = app(CreateHostingAccount::class)
            ->execute($data);

        $operation = ProvisioningOperation::query()
            ->where('resource_id', $hostingAccount->id)
            ->firstOrFail();

        $this->assertSame(
            $client->id,
            $operation->client_id
        );

        $this->assertSame(
            $server->id,
            $operation->server_id
        );

        $this->assertSame(
            $service->id,
            $operation->service_id
        );

        $this->assertSame(
            'create',
            $operation->operation
        );

        $this->assertSame(
            HostingAccount::class,
            $operation->resource_type
        );

        $this->assertSame(
            'pending',
            $operation->status
        );

        $this->assertSame(
            0,
            $operation->attempts
        );

        $this->assertSame(
            'hosting:create:' . $service->id,
            $operation->idempotency_key
        );
    }

    public function test_it_rejects_non_hosting_service(): void
    {
        $client = Client::factory()->create();

        $domainType = ServiceType::where(
            'slug',
            'domain'
        )->firstOrFail();

        $service = Service::factory()
            ->for($client)
            ->state([
                'service_type_id' => $domainType->id,
                'status' => 'pending',
            ])
            ->create();

        $provider = Provider::factory()->cpanel()->create();

        $server = Server::factory()
            ->for($provider)
            ->create([
                'status' => 'active',
            ]);

        $data = new CreateHostingAccountData(
            serviceId: $service->id,
            serverId: $server->id,
            username: 'acmetest',
            primaryDomain: 'acme.test',
        );

        $this->expectException(DomainException::class);

        app(CreateHostingAccount::class)->execute($data);
    }

    public function test_it_rejects_inactive_server(): void
    {
        $client = Client::factory()->create();

        $hostingType = ServiceType::where(
            'slug',
            'hosting'
        )->firstOrFail();

        $service = Service::factory()
            ->for($client)
            ->state([
                'service_type_id' => $hostingType->id,
                'status' => 'pending',
            ])
            ->create();

        $provider = Provider::factory()->cpanel()->create();

        $server = Server::factory()
            ->for($provider)
            ->create([
                'status' => 'maintenance',
            ]);

        $data = new CreateHostingAccountData(
            serviceId: $service->id,
            serverId: $server->id,
            username: 'acmetest',
            primaryDomain: 'acme.test',
        );

        $this->expectException(DomainException::class);

        app(CreateHostingAccount::class)->execute($data);
    }

    public function test_it_rejects_duplicate_hosting_account(): void
    {
        $client = Client::factory()->create();

        $hostingType = ServiceType::where(
            'slug',
            'hosting'
        )->firstOrFail();

        $service = Service::factory()
            ->for($client)
            ->state([
                'service_type_id' => $hostingType->id,
                'status' => 'pending',
            ])
            ->create();

        $provider = Provider::factory()->cpanel()->create();

        $server = Server::factory()
            ->for($provider)
            ->create([
                'status' => 'active',
            ]);

        HostingAccount::factory()
            ->for($service, 'service')
            ->for($server)
            ->create();

        $data = new CreateHostingAccountData(
            serviceId: $service->id,
            serverId: $server->id,
            username: 'anotheruser',
            primaryDomain: 'another.test',
        );

        $this->expectException(DomainException::class);

        app(CreateHostingAccount::class)->execute($data);
    }
}