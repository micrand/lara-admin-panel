<?php

namespace Tests\Feature\Domain;

use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Domain;
use App\Models\DnsRecord;
use App\Models\DnsZone;
use App\Models\EmailAccount;
use App\Models\EmailAlias;
use App\Models\EmailForwarder;
use App\Models\HostingAccount;
use App\Models\Provider;
use App\Models\Server;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientServiceProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_client_service_structure_can_be_created(): void
    {
        /*
         * -------------------------------------------------------------
         * 1. Reference data
         * -------------------------------------------------------------
         */

        $serviceTypeSeeder = new \Database\Seeders\ServiceTypeSeeder();
        $serviceTypeSeeder->run();

        $hostingType = ServiceType::where('slug', 'hosting')->firstOrFail();
        $domainType = ServiceType::where('slug', 'domain')->firstOrFail();
        $emailType = ServiceType::where('slug', 'email')->firstOrFail();
        $dnsType = ServiceType::where('slug', 'dns')->firstOrFail();

        /*
         * -------------------------------------------------------------
         * 2. Provider + Server
         * -------------------------------------------------------------
         */

        $provider = Provider::factory()->cpanel()->create();

        $server = Server::factory()
            ->for($provider)
            ->create([
                'name' => 'cPanel Production',
                'hostname' => 'cpanel.example.test',
                'port' => 2087,
                'environment' => 'production',
            ]);

        /*
         * -------------------------------------------------------------
         * 3. Client
         * -------------------------------------------------------------
         */

        $client = Client::factory()->create([
            'name' => 'Integration Test Company',
            'type' => 'company',
            'status' => 'active',
        ]);

        $contact = ClientContact::factory()
            ->for($client)
            ->primary()
            ->create([
                'first_name' => 'John',
                'last_name' => 'Doe',
            ]);

        /*
         * -------------------------------------------------------------
         * 4. Services
         * -------------------------------------------------------------
         */

        $hostingService = Service::factory()
            ->for($client)
            ->state([
                'service_type_id' => $hostingType->id,
                'name' => 'Web Hosting',
            ])
            ->create();

        $domainService = Service::factory()
            ->for($client)
            ->state([
                'service_type_id' => $domainType->id,
                'name' => 'Domain Registration',
            ])
            ->create();

        $emailService = Service::factory()
            ->for($client)
            ->state([
                'service_type_id' => $emailType->id,
                'name' => 'Professional Email',
            ])
            ->create();

        $dnsService = Service::factory()
            ->for($client)
            ->state([
                'service_type_id' => $dnsType->id,
                'name' => 'DNS Management',
            ])
            ->create();

        /*
         * -------------------------------------------------------------
         * 5. Hosting Account
         * -------------------------------------------------------------
         */

        $hostingAccount = HostingAccount::factory()
            ->for($hostingService, 'service')
            ->for($server)
            ->create([
                'username' => 'integrationtest',
                'primary_domain' => 'integration.test',
            ]);

        /*
         * -------------------------------------------------------------
         * 6. Domain
         * -------------------------------------------------------------
         */

        $domain = Domain::factory()
            ->for($client)
            ->for($domainService, 'service')
            ->for($hostingAccount, 'hostingAccount')
            ->create([
                'name' => 'integration.test',
                'fqdn' => 'integration.test',
                'type' => 'primary',
                'status' => 'active',
            ]);

        /*
         * -------------------------------------------------------------
         * 7. DNS Zone
         * -------------------------------------------------------------
         */

        $dnsZone = DnsZone::factory()
            ->for($domain)
            ->create([
                'name' => 'integration.test',
                'status' => 'active',
            ]);

        $aRecord = DnsRecord::factory()
            ->for($dnsZone, 'dnsZone')
            ->a()
            ->create([
                'name' => '@',
                'value' => '192.0.2.10',
            ]);

        $wwwRecord = DnsRecord::factory()
            ->for($dnsZone, 'dnsZone')
            ->create([
                'type' => 'CNAME',
                'name' => 'www',
                'value' => 'integration.test',
                'ttl' => 3600,
                'status' => 'active',
            ]);

        /*
         * -------------------------------------------------------------
         * 8. Email Account
         * -------------------------------------------------------------
         */

        $emailAccount = EmailAccount::factory()
            ->for($client)
            ->for($emailService, 'service')
            ->for($domain)
            ->for($hostingAccount)
            ->create([
                'local_part' => 'contact',
                'email' => 'contact@integration.test',
                'status' => 'active',
            ]);

        /*
         * -------------------------------------------------------------
         * 9. Email Alias
         * -------------------------------------------------------------
         */

        $emailAlias = EmailAlias::factory()
            ->for($emailAccount)
            ->create([
                'alias' => 'info@integration.test',
                'status' => 'active',
            ]);

        /*
         * -------------------------------------------------------------
         * 10. Email Forwarder
         * -------------------------------------------------------------
         */

        $emailForwarder = EmailForwarder::factory()
            ->for($domain)
            ->create([
                'source' => 'support',
                'destination' => 'contact@integration.test',
                'status' => 'active',
            ]);

        /*
         * -------------------------------------------------------------
         * 11. Database assertions
         * -------------------------------------------------------------
         */

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Integration Test Company',
        ]);

        $this->assertDatabaseHas('client_contacts', [
            'id' => $contact->id,
            'client_id' => $client->id,
        ]);

        $this->assertDatabaseHas('services', [
            'id' => $hostingService->id,
            'client_id' => $client->id,
            'service_type_id' => $hostingType->id,
        ]);

        $this->assertDatabaseHas('hosting_accounts', [
            'id' => $hostingAccount->id,
            'service_id' => $hostingService->id,
            'server_id' => $server->id,
        ]);

        $this->assertDatabaseHas('domains', [
            'id' => $domain->id,
            'client_id' => $client->id,
            'service_id' => $domainService->id,
            'hosting_account_id' => $hostingAccount->id,
        ]);

        $this->assertDatabaseHas('dns_zones', [
            'id' => $dnsZone->id,
            'domain_id' => $domain->id,
        ]);

        $this->assertDatabaseHas('dns_records', [
            'id' => $aRecord->id,
            'dns_zone_id' => $dnsZone->id,
        ]);

        $this->assertDatabaseHas('email_accounts', [
            'id' => $emailAccount->id,
            'client_id' => $client->id,
            'domain_id' => $domain->id,
            'hosting_account_id' => $hostingAccount->id,
        ]);

        $this->assertDatabaseHas('email_aliases', [
            'id' => $emailAlias->id,
            'email_account_id' => $emailAccount->id,
        ]);

        $this->assertDatabaseHas('email_forwarders', [
            'id' => $emailForwarder->id,
            'domain_id' => $domain->id,
        ]);

        /*
         * -------------------------------------------------------------
         * 12. Relationship assertions
         * -------------------------------------------------------------
         */

        $this->assertTrue(
            $client->services->contains($hostingService)
        );

        $this->assertTrue(
            $client->contacts->contains($contact)
        );

        $this->assertTrue(
            $hostingService->hostingAccount->is($hostingAccount)
        );

        $this->assertTrue(
            $hostingAccount->server->is($server)
        );

        $this->assertTrue(
            $domain->hostingAccount->is($hostingAccount)
        );

        $this->assertTrue(
            $domain->dnsZone->is($dnsZone)
        );

        $this->assertTrue(
            $dnsZone->records->contains($aRecord)
        );

        $this->assertTrue(
            $emailAccount->aliases->contains($emailAlias)
        );

        $this->assertTrue(
            $domain->emailForwarders->contains($emailForwarder)
        );

        /*
         * -------------------------------------------------------------
         * 13. Final count assertions
         * -------------------------------------------------------------
         */

        $this->assertCount(4, $client->services);

        $this->assertCount(1, $client->contacts);

        $this->assertCount(2, $dnsZone->records);

        $this->assertCount(1, $emailAccount->aliases);
    }
}