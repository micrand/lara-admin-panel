<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Domain;
use App\Models\DomainAlias;
use App\Models\DnsRecord;
use App\Models\DnsZone;
use App\Models\EmailAccount;
use App\Models\EmailAlias;
use App\Models\EmailForwarder;
use App\Models\HostingAccount;
use App\Models\Permission;
use App\Models\Provider;
use App\Models\ProviderCredential;
use App\Models\ProvisioningOperation;
use App\Models\Role;
use App\Models\Server;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\SyncConflict;
use App\Models\SyncRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tests\TestCase;

class ModelRelationsTest extends TestCase
{
    public function test_core_relationships_are_defined(): void
    {
        $this->assertInstanceOf(
            BelongsToMany::class,
            (new User())->roles()
        );

        $this->assertInstanceOf(
            BelongsToMany::class,
            (new Role())->users()
        );

        $this->assertInstanceOf(
            BelongsToMany::class,
            (new Role())->permissions()
        );

        $this->assertInstanceOf(
            BelongsToMany::class,
            (new Permission())->roles()
        );

        $this->assertInstanceOf(
            HasMany::class,
            (new Client())->contacts()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new ClientContact())->client()
        );

        $this->assertInstanceOf(
            HasMany::class,
            (new Client())->services()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new Service())->client()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new Service())->serviceType()
        );

        $this->assertInstanceOf(
            HasMany::class,
            (new ServiceType())->services()
        );
    }

    public function test_infrastructure_relationships_are_defined(): void
    {
        $this->assertInstanceOf(
            HasMany::class,
            (new Provider())->servers()
        );

        $this->assertInstanceOf(
            HasMany::class,
            (new Provider())->credentials()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new Server())->provider()
        );

        $this->assertInstanceOf(
            HasMany::class,
            (new Server())->credentials()
        );

        $this->assertInstanceOf(
            HasMany::class,
            (new Server())->hostingAccounts()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new ProviderCredential())->provider()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new ProviderCredential())->server()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new HostingAccount())->service()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new HostingAccount())->server()
        );
    }

    public function test_domain_dns_and_email_relationships_are_defined(): void
    {
        $this->assertInstanceOf(
            BelongsTo::class,
            (new Domain())->client()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new Domain())->service()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new Domain())->hostingAccount()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new Domain())->parentDomain()
        );

        $this->assertInstanceOf(
            HasMany::class,
            (new Domain())->childDomains()
        );

        $this->assertInstanceOf(
            HasMany::class,
            (new Domain())->aliases()
        );

        $this->assertInstanceOf(
            HasOne::class,
            (new Domain())->dnsZone()
        );

        $this->assertInstanceOf(
            HasMany::class,
            (new Domain())->emailAccounts()
        );

        $this->assertInstanceOf(
            HasMany::class,
            (new Domain())->emailForwarders()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new DomainAlias())->domain()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new DomainAlias())->aliasDomain()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new DnsZone())->domain()
        );

        $this->assertInstanceOf(
            HasMany::class,
            (new DnsZone())->records()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new DnsRecord())->dnsZone()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new EmailAccount())->client()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new EmailAccount())->service()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new EmailAccount())->domain()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new EmailAccount())->hostingAccount()
        );

        $this->assertInstanceOf(
            HasMany::class,
            (new EmailAccount())->aliases()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new EmailAlias())->emailAccount()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new EmailForwarder())->domain()
        );
    }

    public function test_provisioning_and_synchronization_relationships_are_defined(): void
    {
        $this->assertInstanceOf(
            BelongsTo::class,
            (new ProvisioningOperation())->client()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new ProvisioningOperation())->server()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new ProvisioningOperation())->service()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new SyncRun())->server()
        );

        $this->assertInstanceOf(
            HasMany::class,
            (new SyncRun())->conflicts()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new SyncConflict())->syncRun()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new SyncConflict())->server()
        );

        $this->assertInstanceOf(
            BelongsTo::class,
            (new SyncConflict())->resolvedBy()
        );
    }
}