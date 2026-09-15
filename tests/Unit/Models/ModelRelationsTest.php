<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Domain;
use App\Models\DnsZone;
use App\Models\EmailAccount;
use App\Models\HostingAccount;
use App\Models\Provider;
use App\Models\Server;
use App\Models\Service;
use App\Models\ServiceType;
use Tests\TestCase;

class ModelRelationsTest extends TestCase
{
    public function test_client_relations(): void
    {
        $model = new Client();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $model->contacts()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $model->services()
        );
    }

    public function test_service_relations(): void
    {
        $model = new Service();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $model->client()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $model->serviceType()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasOne::class,
            $model->hostingAccount()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $model->domains()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $model->emailAccounts()
        );
    }

    public function test_provider_relations(): void
    {
        $model = new Provider();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $model->servers()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $model->credentials()
        );
    }

    public function test_server_relations(): void
    {
        $model = new Server();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $model->provider()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $model->hostingAccounts()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $model->provisioningOperations()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $model->syncRuns()
        );
    }

    public function test_domain_relations(): void
    {
        $model = new Domain();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $model->client()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $model->service()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $model->parentDomain()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $model->childDomains()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasOne::class,
            $model->dnsZone()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $model->emailAccounts()
        );
    }

    public function test_dns_relations(): void
    {
        $model = new DnsZone();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $model->domain()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $model->records()
        );
    }

    public function test_email_relations(): void
    {
        $model = new EmailAccount();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $model->client()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $model->service()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $model->domain()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $model->hostingAccount()
        );

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $model->aliases()
        );
    }
}