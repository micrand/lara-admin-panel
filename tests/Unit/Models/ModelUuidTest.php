<?php

namespace Tests\Unit\Models;

use App\Models\AuditLog;
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
use App\Models\Notification;
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
use Tests\TestCase;

class ModelUuidTest extends TestCase
{
    public function test_all_domain_models_use_non_incrementing_string_keys(): void
    {
        $models = [
            User::class,
            Role::class,
            Permission::class,
            Client::class,
            ClientContact::class,
            ServiceType::class,
            Service::class,
            Provider::class,
            Server::class,
            ProviderCredential::class,
            HostingAccount::class,
            Domain::class,
            DomainAlias::class,
            DnsZone::class,
            DnsRecord::class,
            EmailAccount::class,
            EmailAlias::class,
            EmailForwarder::class,
            ProvisioningOperation::class,
            SyncRun::class,
            SyncConflict::class,
            AuditLog::class,
            Notification::class,
        ];

        foreach ($models as $modelClass) {
            $model = new $modelClass();

            $this->assertSame(
                'string',
                $model->getKeyType(),
                "{$modelClass} must use string UUID keys."
            );

            $this->assertFalse(
                $model->getIncrementing(),
                "{$modelClass} must not use auto-incrementing IDs."
            );
        }
    }
}