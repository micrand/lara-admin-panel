<?php

namespace Tests\Feature\Application\Dns;

use App\Application\Dns\Actions\CreateDnsRecord;
use App\Application\Dns\DTOs\CreateDnsRecordData;
use App\Models\Client;
use App\Models\Domain;
use App\Models\DnsRecord;
use App\Models\DnsZone;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateDnsRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_a_record(): void
    {
        $zone = $this->createDnsZone();

        $record = app(CreateDnsRecord::class)->execute(
            new CreateDnsRecordData(
                dnsZoneId: $zone->id,
                type: 'A',
                name: '@',
                value: '192.0.2.10',
            )
        );

        $this->assertInstanceOf(DnsRecord::class, $record);

        $this->assertDatabaseHas('dns_records', [
            'id' => $record->id,
            'dns_zone_id' => $zone->id,
            'type' => 'A',
            'name' => '@',
            'value' => '192.0.2.10',
            'ttl' => 3600,
            'status' => 'pending',
        ]);
    }

    public function test_it_normalizes_record_type_and_name(): void
    {
        $zone = $this->createDnsZone();

        $record = app(CreateDnsRecord::class)->execute(
            new CreateDnsRecordData(
                dnsZoneId: $zone->id,
                type: 'cname',
                name: 'WWW',
                value: 'example.com.',
            )
        );

        $this->assertSame('CNAME', $record->type);
        $this->assertSame('www', $record->name);
        $this->assertSame('example.com.', $record->value);
    }

    public function test_it_creates_an_mx_record_with_priority(): void
    {
        $zone = $this->createDnsZone();

        $record = app(CreateDnsRecord::class)->execute(
            new CreateDnsRecordData(
                dnsZoneId: $zone->id,
                type: 'MX',
                name: '@',
                value: 'mail.example.com.',
                priority: 10,
            )
        );

        $this->assertSame('MX', $record->type);
        $this->assertSame(10, $record->priority);
    }

    public function test_it_creates_an_srv_record(): void
    {
        $zone = $this->createDnsZone();

        $record = app(CreateDnsRecord::class)->execute(
            new CreateDnsRecordData(
                dnsZoneId: $zone->id,
                type: 'SRV',
                name: '_sip._tcp',
                value: 'sip.example.com.',
                priority: 10,
                weight: 5,
                port: 5060,
            )
        );

        $this->assertSame('SRV', $record->type);
        $this->assertSame(10, $record->priority);
        $this->assertSame(5, $record->weight);
        $this->assertSame(5060, $record->port);
    }

    public function test_it_rejects_an_invalid_record_type(): void
    {
        $zone = $this->createDnsZone();

        $this->expectException(ValidationException::class);

        app(CreateDnsRecord::class)->execute(
            new CreateDnsRecordData(
                dnsZoneId: $zone->id,
                type: 'INVALID',
                name: '@',
                value: 'example.com',
            )
        );
    }

    public function test_it_rejects_an_mx_record_without_priority(): void
    {
        $zone = $this->createDnsZone();

        $this->expectException(ValidationException::class);

        app(CreateDnsRecord::class)->execute(
            new CreateDnsRecordData(
                dnsZoneId: $zone->id,
                type: 'MX',
                name: '@',
                value: 'mail.example.com.',
            )
        );
    }

    public function test_it_rejects_an_srv_record_without_required_fields(): void
    {
        $zone = $this->createDnsZone();

        $this->expectException(ValidationException::class);

        app(CreateDnsRecord::class)->execute(
            new CreateDnsRecordData(
                dnsZoneId: $zone->id,
                type: 'SRV',
                name: '_sip._tcp',
                value: 'sip.example.com.',
            )
        );
    }

    public function test_it_rejects_an_invalid_ttl(): void
    {
        $zone = $this->createDnsZone();

        $this->expectException(ValidationException::class);

        app(CreateDnsRecord::class)->execute(
            new CreateDnsRecordData(
                dnsZoneId: $zone->id,
                type: 'A',
                name: '@',
                value: '192.0.2.10',
                ttl: 10,
            )
        );
    }

    public function test_it_rejects_a_missing_dns_zone(): void
    {
        $this->expectException(ValidationException::class);

        app(CreateDnsRecord::class)->execute(
            new CreateDnsRecordData(
                dnsZoneId: '01900000-0000-7000-8000-000000000000',
                type: 'A',
                name: '@',
                value: '192.0.2.10',
            )
        );
    }

    private function createDnsZone(): DnsZone
    {
        $client = Client::factory()->create();

        $serviceType = ServiceType::factory()->create([
            'name' => 'Domain',
            'slug' => 'domain',
            'category' => 'domain',
            'is_active' => true,
        ]);

        $service = Service::factory()->create([
            'client_id' => $client->id,
            'service_type_id' => $serviceType->id,
            'status' => 'active',
        ]);

        $domain = Domain::factory()->create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'fqdn' => 'example.com',
            'status' => 'active',
        ]);

        return DnsZone::factory()->create([
            'domain_id' => $domain->id,
            'name' => 'example.com',
            'status' => 'active',
        ]);
    }
}