<?php

namespace Tests\Unit\Models;

use Tests\TestCase;

class ModelExistenceTest extends TestCase
{
    public function test_all_domain_models_exist(): void
    {
        $models = [
            'User',
            'Role',
            'Permission',
            'Client',
            'ClientContact',
            'ServiceType',
            'Service',
            'Provider',
            'Server',
            'ProviderCredential',
            'HostingAccount',
            'Domain',
            'DomainAlias',
            'DnsZone',
            'DnsRecord',
            'EmailAccount',
            'EmailAlias',
            'EmailForwarder',
            'ProvisioningOperation',
            'SyncRun',
            'SyncConflict',
            'AuditLog',
            'Notification',
        ];

        foreach ($models as $model) {
            $class = "App\\Models\\{$model}";

            $this->assertTrue(
                class_exists($class),
                "Model {$class} does not exist."
            );
        }
    }
}