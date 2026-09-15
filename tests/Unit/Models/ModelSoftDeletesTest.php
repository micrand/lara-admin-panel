<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\Domain;
use App\Models\EmailAccount;
use App\Models\HostingAccount;
use App\Models\Service;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class ModelSoftDeletesTest extends TestCase
{
    public function test_expected_models_use_soft_deletes(): void
    {
        $models = [
            Client::class,
            Service::class,
            HostingAccount::class,
            Domain::class,
            EmailAccount::class,
        ];

        foreach ($models as $modelClass) {
            $model = new $modelClass();

            $this->assertContains(
                SoftDeletes::class,
                class_uses_recursive($model),
                "{$modelClass} must use SoftDeletes."
            );
        }
    }
}