<?php

namespace Tests\Feature\Application\Email;

use App\Application\Email\Actions\CreateEmailAccount;
use App\Application\Email\DTOs\CreateEmailAccountData;
use App\Models\Client;
use App\Models\Domain;
use App\Models\EmailAccount;
use App\Models\HostingAccount;
use App\Models\Provider;
use App\Models\Server;
use App\Models\Service;
use App\Models\ServiceType;
use Database\Seeders\ServiceTypeSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateEmailAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ServiceTypeSeeder::class);
    }

    private function createEmailContext(): array
    {
        $client = Client::factory()->create();

        $emailType = ServiceType::where(
            'slug',
            'email'
        )->firstOrFail();

        $domainType = ServiceType::where(
            'slug',
            'domain'
        )->firstOrFail();

        $hostingType = ServiceType::where(
            'slug',
            'hosting'
        )->firstOrFail();

        $emailService = Service::factory()
            ->for($client)
            ->state([
                'service_type_id' => $emailType->id,
                'status' => 'pending',
            ])
            ->create();

        $domainService = Service::factory()
            ->for($client)
            ->state([
                'service_type_id' => $domainType->id,
                'status' => 'active',
            ])
            ->create();

        $hostingService = Service::factory()
            ->for($client)
            ->state([
                'service_type_id' => $hostingType->id,
                'status' => 'active',
            ])
            ->create();

        $domain = Domain::factory()
            ->for($client)
            ->for($domainService, 'service')
            ->create([
                'fqdn' => 'example.test',
                'name' => 'example.test',
                'type' => 'primary',
                'status' => 'active',
            ]);

        $provider = Provider::factory()
            ->cpanel()
            ->create();

        $server = Server::factory()
            ->for($provider)
            ->create([
                'status' => 'active',
            ]);

        $hostingAccount = HostingAccount::factory()
            ->for($hostingService, 'service')
            ->for($server)
            ->create([
                'status' => 'active',
            ]);

        return [
            'client' => $client,
            'emailService' => $emailService,
            'domain' => $domain,
            'hostingAccount' => $hostingAccount,
        ];
    }

    public function test_it_creates_a_pending_email_account(): void
    {
        $context = $this->createEmailContext();

        $data = new CreateEmailAccountData(
            clientId: $context['client']->id,
            serviceId: $context['emailService']->id,
            domainId: $context['domain']->id,
            hostingAccountId: $context['hostingAccount']->id,
            localPart: 'contact',
            quota: 10240,
        );

        $emailAccount = app(CreateEmailAccount::class)
            ->execute($data);

        $this->assertInstanceOf(
            EmailAccount::class,
            $emailAccount
        );

        $this->assertSame(
            'contact',
            $emailAccount->local_part
        );

        $this->assertSame(
            'contact@example.test',
            $emailAccount->email
        );

        $this->assertSame(
            10240,
            $emailAccount->quota
        );

        $this->assertSame(
            'pending',
            $emailAccount->status
        );

        $this->assertDatabaseHas('email_accounts', [
            'id' => $emailAccount->id,
            'client_id' => $context['client']->id,
            'service_id' => $context['emailService']->id,
            'domain_id' => $context['domain']->id,
            'hosting_account_id' => $context['hostingAccount']->id,
            'local_part' => 'contact',
            'email' => 'contact@example.test',
            'status' => 'pending',
        ]);
    }

    public function test_it_rejects_non_email_service(): void
    {
        $context = $this->createEmailContext();

        $hostingType = ServiceType::where(
            'slug',
            'hosting'
        )->firstOrFail();

        $wrongService = Service::factory()
            ->for($context['client'])
            ->state([
                'service_type_id' => $hostingType->id,
                'status' => 'active',
            ])
            ->create();

        $data = new CreateEmailAccountData(
            clientId: $context['client']->id,
            serviceId: $wrongService->id,
            domainId: $context['domain']->id,
            hostingAccountId: $context['hostingAccount']->id,
            localPart: 'contact',
        );

        $this->expectException(DomainException::class);

        app(CreateEmailAccount::class)->execute($data);
    }

    public function test_it_rejects_domain_from_another_client(): void
    {
        $context = $this->createEmailContext();

        $otherClient = Client::factory()->create();

        $domainType = ServiceType::where(
            'slug',
            'domain'
        )->firstOrFail();

        $domainService = Service::factory()
            ->for($otherClient)
            ->state([
                'service_type_id' => $domainType->id,
                'status' => 'active',
            ])
            ->create();

        $otherDomain = Domain::factory()
            ->for($otherClient)
            ->for($domainService, 'service')
            ->create([
                'fqdn' => 'other.test',
                'name' => 'other.test',
                'type' => 'primary',
                'status' => 'active',
            ]);

        $data = new CreateEmailAccountData(
            clientId: $context['client']->id,
            serviceId: $context['emailService']->id,
            domainId: $otherDomain->id,
            hostingAccountId: $context['hostingAccount']->id,
            localPart: 'contact',
        );

        $this->expectException(
            \Illuminate\Database\Eloquent\ModelNotFoundException::class
        );

        app(CreateEmailAccount::class)->execute($data);
    }

    public function test_it_rejects_duplicate_email_account(): void
    {
        $context = $this->createEmailContext();

        EmailAccount::factory()
            ->for($context['client'])
            ->for($context['emailService'], 'service')
            ->for($context['domain'])
            ->for($context['hostingAccount'])
            ->create([
                'local_part' => 'contact',
                'email' => 'contact@example.test',
                'status' => 'active',
            ]);

        $data = new CreateEmailAccountData(
            clientId: $context['client']->id,
            serviceId: $context['emailService']->id,
            domainId: $context['domain']->id,
            hostingAccountId: $context['hostingAccount']->id,
            localPart: 'CONTACT',
        );

        $this->expectException(DomainException::class);

        app(CreateEmailAccount::class)->execute($data);
    }

    public function test_it_rejects_invalid_local_part(): void
    {
        $context = $this->createEmailContext();

        $data = new CreateEmailAccountData(
            clientId: $context['client']->id,
            serviceId: $context['emailService']->id,
            domainId: $context['domain']->id,
            hostingAccountId: $context['hostingAccount']->id,
            localPart: 'invalid local part',
        );

        $this->expectException(DomainException::class);

        app(CreateEmailAccount::class)->execute($data);
    }

    public function test_it_rejects_inactive_hosting_account(): void
    {
        $context = $this->createEmailContext();

        $context['hostingAccount']->update([
            'status' => 'suspended',
        ]);

        $data = new CreateEmailAccountData(
            clientId: $context['client']->id,
            serviceId: $context['emailService']->id,
            domainId: $context['domain']->id,
            hostingAccountId: $context['hostingAccount']->id,
            localPart: 'contact',
        );

        $this->expectException(DomainException::class);

        app(CreateEmailAccount::class)->execute($data);
    }
}