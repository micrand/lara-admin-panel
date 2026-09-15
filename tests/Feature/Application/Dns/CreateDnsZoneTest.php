<?php

namespace Tests\Feature\Application\Dns;

use App\Application\Dns\Actions\CreateDnsZone;
use App\Application\Dns\DTOs\CreateDnsZoneData;
use App\Models\Client;
use App\Models\Domain;
use App\Models\DnsZone;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateDnsZoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_dns_zone_for_an_active_domain(): void
    {
        $client = Client::factory()->create();

        $domainServiceType = $this->createDomainServiceType();

        $service = Service::factory()->create([
            'client_id' => $client->id,
            'service_type_id' => $domainServiceType->id,
            'status' => 'active',
        ]);

        $domain = Domain::factory()->create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'fqdn' => 'example.com',
            'status' => 'active',
        ]);

        $zone = app(CreateDnsZone::class)->execute(
            new CreateDnsZoneData(
                domainId: $domain->id,
            )
        );

        $this->assertInstanceOf(DnsZone::class, $zone);

        $this->assertDatabaseHas('dns_zones', [
            'id' => $zone->id,
            'domain_id' => $domain->id,
            'name' => 'example.com',
            'status' => 'pending',
        ]);
    }

    public function test_it_accepts_a_provider_zone_id(): void
    {
        $client = Client::factory()->create();

        $serviceType = $this->createDomainServiceType();

        $service = Service::factory()->create([
            'client_id' => $client->id,
            'service_type_id' => $serviceType->id,
        ]);

        $domain = Domain::factory()->create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'fqdn' => 'example.org',
            'status' => 'active',
        ]);

        $zone = app(CreateDnsZone::class)->execute(
            new CreateDnsZoneData(
                domainId: $domain->id,
                providerZoneId: 'cpanel-zone-123',
            )
        );

        $this->assertSame(
            'cpanel-zone-123',
            $zone->provider_zone_id
        );
    }

    public function test_it_rejects_a_missing_domain(): void
    {
        $this->expectException(ValidationException::class);

        app(CreateDnsZone::class)->execute(
            new CreateDnsZoneData(
                domainId: '01900000-0000-7000-8000-000000000000',
            )
        );
    }

    public function test_it_rejects_a_deleted_domain(): void
    {
        $client = Client::factory()->create();

        $serviceType = $this->createDomainServiceType();

        $service = Service::factory()->create([
            'client_id' => $client->id,
            'service_type_id' => $serviceType->id,
        ]);

        $domain = Domain::factory()->create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'status' => 'active',
        ]);

        $domain->delete();

        $this->expectException(ValidationException::class);

        app(CreateDnsZone::class)->execute(
            new CreateDnsZoneData(
                domainId: $domain->id,
            )
        );
    }

    public function test_it_rejects_duplicate_dns_zone(): void
    {
        $client = Client::factory()->create();

        $serviceType = $this->createDomainServiceType();

        $service = Service::factory()->create([
            'client_id' => $client->id,
            'service_type_id' => $serviceType->id,
        ]);

        $domain = Domain::factory()->create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'status' => 'active',
        ]);

        DnsZone::factory()->create([
            'domain_id' => $domain->id,
        ]);

        $this->expectException(ValidationException::class);

        app(CreateDnsZone::class)->execute(
            new CreateDnsZoneData(
                domainId: $domain->id,
            )
        );
    }

    private function createDomainServiceType(): ServiceType
    {
        return ServiceType::factory()->create([
            'name' => 'Domain',
            'slug' => 'domain',
            'category' => 'domain',
            'is_active' => true,
        ]);
    }
}