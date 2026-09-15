<?php

namespace Database\Seeders;

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
use App\Models\Role;
use App\Models\Server;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * -------------------------------------------------------------
         * 1. Reference data
         * -------------------------------------------------------------
         */

        $this->call([
            RolePermissionSeeder::class,
            ServiceTypeSeeder::class,
        ]);

        /*
         * -------------------------------------------------------------
         * 2. Users
         * -------------------------------------------------------------
         */

        $admin = User::factory()->create([
            'name' => 'System Administrator',
            'email' => 'admin@example.test',
            'password' => Hash::make('password'),
        ]);

        $admin->roles()->sync([
            Role::where('slug', 'super-admin')->value('id') => [
                'created_at' => now(),
            ],
        ]);

        /*
         * -------------------------------------------------------------
         * 3. Providers
         * -------------------------------------------------------------
         */

        $cpanel = Provider::factory()
            ->cpanel()
            ->create([
                'capabilities' => [
                    'hosting' => true,
                    'domains' => true,
                    'dns' => true,
                    'email' => true,
                ],
            ]);

        $plesk = Provider::factory()
            ->plesk()
            ->create([
                'capabilities' => [
                    'hosting' => true,
                    'domains' => true,
                    'dns' => true,
                    'email' => true,
                ],
            ]);

        /*
         * -------------------------------------------------------------
         * 4. Servers
         * -------------------------------------------------------------
         */

        $cpanelProduction = Server::factory()->create([
            'provider_id' => $cpanel->id,
            'name' => 'cPanel Production',
            'hostname' => 'cpanel-prod.example.test',
            'ip' => '192.0.2.10',
            'port' => 2087,
            'environment' => 'production',
        ]);

        $cpanelStaging = Server::factory()->staging()->create([
            'provider_id' => $cpanel->id,
            'name' => 'cPanel Staging',
            'hostname' => 'cpanel-staging.example.test',
            'ip' => '192.0.2.11',
            'port' => 2087,
            'environment' => 'staging',
        ]);

        $pleskProduction = Server::factory()->create([
            'provider_id' => $plesk->id,
            'name' => 'Plesk Production',
            'hostname' => 'plesk-prod.example.test',
            'ip' => '192.0.2.20',
            'port' => 8443,
            'environment' => 'production',
        ]);

        /*
         * -------------------------------------------------------------
         * 5. Clients
         * -------------------------------------------------------------
         */

        $client = Client::factory()->create([
            'type' => 'company',
            'name' => 'Demo Company',
            'legal_name' => 'Demo Company SAS',
            'email' => 'contact@example.test',
            'country_code' => 'FR',
        ]);

        ClientContact::factory()->primary()->create([
            'client_id' => $client->id,
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@example.test',
            'position' => 'Technical Director',
        ]);

        /*
         * -------------------------------------------------------------
         * 6. Services
         * -------------------------------------------------------------
         */

        $hostingService = Service::factory()
            ->hosting()
            ->create([
                'client_id' => $client->id,
                'name' => 'Professional Hosting',
                'reference' => 'SRV-HOST-001',
            ]);

        $domainService = Service::factory()
            ->domain()
            ->create([
                'client_id' => $client->id,
                'name' => 'Domain Management',
                'reference' => 'SRV-DOM-001',
            ]);

        $emailService = Service::factory()
            ->email()
            ->create([
                'client_id' => $client->id,
                'name' => 'Professional Email',
                'reference' => 'SRV-MAIL-001',
            ]);

        $dnsService = Service::factory()
            ->dns()
            ->create([
                'client_id' => $client->id,
                'name' => 'DNS Management',
                'reference' => 'SRV-DNS-001',
            ]);

        /*
         * -------------------------------------------------------------
         * 7. Hosting account
         * -------------------------------------------------------------
         */

        $hostingAccount = HostingAccount::factory()->create([
            'service_id' => $hostingService->id,
            'server_id' => $cpanelProduction->id,
            'username' => 'democompany',
            'primary_domain' => 'example.test',
        ]);

        /*
         * -------------------------------------------------------------
         * 8. Domain
         * -------------------------------------------------------------
         */

        $domain = Domain::factory()->create([
            'client_id' => $client->id,
            'service_id' => $domainService->id,
            'hosting_account_id' => $hostingAccount->id,
            'name' => 'example.test',
            'fqdn' => 'example.test',
            'type' => 'primary',
        ]);

        /*
         * -------------------------------------------------------------
         * 9. DNS Zone
         * -------------------------------------------------------------
         */

        $dnsZone = DnsZone::factory()->create([
            'domain_id' => $domain->id,
            'name' => $domain->fqdn,
        ]);

        DnsRecord::factory()
            ->a()
            ->create([
                'dns_zone_id' => $dnsZone->id,
                'name' => '@',
                'value' => '192.0.2.10',
            ]);

        DnsRecord::factory()
            ->cname()
            ->create([
                'dns_zone_id' => $dnsZone->id,
                'name' => 'www',
                'value' => $domain->fqdn,
            ]);

        DnsRecord::factory()
            ->mx()
            ->create([
                'dns_zone_id' => $dnsZone->id,
                'name' => '@',
                'value' => 'mail.example.test',
                'priority' => 10,
            ]);

        DnsRecord::factory()
            ->txt()
            ->create([
                'dns_zone_id' => $dnsZone->id,
                'name' => '@',
            ]);

        /*
         * -------------------------------------------------------------
         * 10. Email account
         * -------------------------------------------------------------
         */

        $emailAccount = EmailAccount::factory()->create([
            'client_id' => $client->id,
            'service_id' => $emailService->id,
            'domain_id' => $domain->id,
            'hosting_account_id' => $hostingAccount->id,
            'local_part' => 'admin',
            'email' => 'admin@example.test',
            'quota' => 10240,
        ]);

        EmailAlias::factory()->create([
            'email_account_id' => $emailAccount->id,
            'alias' => 'contact@example.test',
        ]);

        EmailForwarder::factory()->create([
            'domain_id' => $domain->id,
            'source' => 'support',
            'destination' => 'admin@example.test',
        ]);

        /*
         * -------------------------------------------------------------
         * 11. Additional demo clients
         * -------------------------------------------------------------
         */

        Client::factory()
            ->count(5)
            ->create()
            ->each(function (Client $client) use ($cpanelProduction): void {
                ClientContact::factory()->primary()->create([
                    'client_id' => $client->id,
                ]);

                $service = Service::factory()
                    ->hosting()
                    ->create([
                        'client_id' => $client->id,
                    ]);

                HostingAccount::factory()->create([
                    'service_id' => $service->id,
                    'server_id' => $cpanelProduction->id,
                ]);
            });

        /*
         * -------------------------------------------------------------
         * 12. Prevent unused variable warnings in IDEs
         * -------------------------------------------------------------
         */

        unset(
            $admin,
            $cpanelStaging,
            $pleskProduction,
            $dnsService
        );
    }
}