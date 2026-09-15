<?php

namespace Tests\Unit\Models;

use Tests\TestCase;

class ModelsExistenceTest extends TestCase
{
    public function test_all_expected_models_exist(): void
    {
        $models = [
            \App\Models\User::class,
            \App\Models\Role::class,
            \App\Models\Permission::class,
            \App\Models\Client::class,
            \App\Models\ClientContact::class,
            \App\Models\ServiceType::class,
            \App\Models\Service::class,
            \App\Models\Provider::class,
            \App\Models\Server::class,
            \App\Models\ProviderCredential::class,
            \App\Models\HostingAccount::class,
            \App\Models\Domain::class,
            \App\Models\DomainAlias::class,
            \App\Models\DnsZone::class,
            \App\Models\DnsRecord::class,
            \App\Models\EmailAccount::class,
            \App\Models\EmailAlias::class,
            \App\Models\EmailForwarder::class,
            \App\Models\ProvisioningOperation::class,
            \App\Models\SyncRun::class,
            \App\Models\SyncConflict::class,
            \App\Models\AuditLog::class,
            \App\Models\Notification::class,
        ];

        foreach ($models as $model) {
            $this->assertTrue(
                class_exists($model),
                "Model {$model} does not exist."
            );
        }
    }
}