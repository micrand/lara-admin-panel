<?php

namespace Tests\Feature\Application\Domain;

use App\Application\Domain\Actions\CreateDomain;
use App\Application\Domain\DTOs\CreateDomainData;
use App\Models\Client;
use App\Models\Domain;
use App\Models\HostingAccount;
use App\Models\Provider;
use App\Models\Server;
use App\Models\Service;
use App\Models\ServiceType;
use Database\Seeders\ServiceTypeSeeder;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateDomainTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ServiceTypeSeeder::class);
    }

    public function test_it_creates_a_primary_domain(): void
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

        $data = new CreateDomainData(
            clientId: $client->id,
            serviceId: $service->id,
            name: 'example.com',
        );

        $domain = app(CreateDomain::class)->execute($data);

        $this->assertInstanceOf(Domain::class, $domain);

        $this->assertSame($client->id, $domain->client_id);
        $this->assertSame($service->id, $domain->service_id);
        $this->assertSame('example.com', $domain->name);
        $this->assertSame('example.com', $domain->fqdn);
        $this->assertSame('primary', $domain->type);
        $this->assertSame('pending', $domain->status);
        $this->assertNull($domain->parent_domain_id);

        $this->assertDatabaseHas('domains', [
            'id' => $domain->id,
            'client_id' => $client->id,
            'service_id' => $service->id,
            'fqdn' => 'example.com',
            'type' => 'primary',
            'status' => 'pending',
        ]);
    }

    public function test_it_creates_a_subdomain_from_parent_domain(): void
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

        $parentDomain = Domain::factory()
            ->for($client)
            ->for($service, 'service')
            ->create([
                'name' => 'example.com',
                'fqdn' => 'example.com',
                'type' => 'primary',
                'status' => 'active',
            ]);

        $data = new CreateDomainData(
            clientId: $client->id,
            serviceId: $service->id,
            name: 'blog',
            type: 'subdomain',
            parentDomainId: $parentDomain->id,
        );

        $domain = app(CreateDomain::class)->execute($data);

        $this->assertSame('blog', $domain->name);
        $this->assertSame('blog.example.com', $domain->fqdn);
        $this->assertSame('subdomain', $domain->type);
        $this->assertSame(
            $parentDomain->id,
            $domain->parent_domain_id
        );
    }

    public function test_it_rejects_subdomain_without_parent(): void
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

        $data = new CreateDomainData(
            clientId: $client->id,
            serviceId: $service->id,
            name: 'blog',
            type: 'subdomain',
        );

        $this->expectException(DomainException::class);

        app(CreateDomain::class)->execute($data);
    }

    public function test_it_rejects_non_domain_service(): void
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

        $data = new CreateDomainData(
            clientId: $client->id,
            serviceId: $service->id,
            name: 'example.com',
        );

        $this->expectException(DomainException::class);

        app(CreateDomain::class)->execute($data);
    }

    public function test_it_rejects_duplicate_domain_case_insensitively(): void
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

        Domain::factory()
            ->for($client)
            ->for($service, 'service')
            ->create([
                'name' => 'Example.com',
                'fqdn' => 'Example.com',
                'type' => 'primary',
                'status' => 'active',
            ]);

        $data = new CreateDomainData(
            clientId: $client->id,
            serviceId: $service->id,
            name: 'example.com',
        );

        $this->expectException(DomainException::class);

        app(CreateDomain::class)->execute($data);
    }

    public function test_it_can_attach_domain_to_a_hosting_account(): void
    {
        $client = Client::factory()->create();

        $domainType = ServiceType::where(
            'slug',
            'domain'
        )->firstOrFail();

        $hostingType = ServiceType::where(
            'slug',
            'hosting'
        )->firstOrFail();

        $domainService = Service::factory()
            ->for($client)
            ->state([
                'service_type_id' => $domainType->id,
                'status' => 'pending',
            ])
            ->create();

        $hostingService = Service::factory()
            ->for($client)
            ->state([
                'service_type_id' => $hostingType->id,
                'status' => 'active',
            ])
            ->create();

        $provider = Provider::factory()->cpanel()->create();

        $server = Server::factory()
            ->for($provider)
            ->create([
                'status' => 'active',
            ]);

        $hostingAccount = HostingAccount::factory()
            ->for($hostingService, 'service')
            ->for($server)
            ->create();

        $data = new CreateDomainData(
            clientId: $client->id,
            serviceId: $domainService->id,
            name: 'example.com',
            hostingAccountId: $hostingAccount->id,
        );

        $domain = app(CreateDomain::class)->execute($data);

        $this->assertSame(
            $hostingAccount->id,
            $domain->hosting_account_id
        );
    }
}