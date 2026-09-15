<?php

namespace Tests\Unit\Application\Provisioning;

use App\Application\Provisioning\Services\ProvisioningOperationService;
use App\Models\ProvisioningOperation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProvisioningOperationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_starts_a_pending_operation(): void
    {
        $operation = ProvisioningOperation::factory()->create([
            'status' => 'pending',
            'attempts' => 0,
        ]);

        $result = app(ProvisioningOperationService::class)
            ->start($operation);

        $this->assertSame('processing', $result->status);
        $this->assertSame(1, $result->attempts);
        $this->assertNotNull($result->started_at);
    }

    public function test_it_completes_a_processing_operation(): void
    {
        $operation = ProvisioningOperation::factory()->create([
            'status' => 'processing',
            'attempts' => 1,
        ]);

        $result = app(ProvisioningOperationService::class)
            ->complete($operation, [
                'external_id' => 'cpanel-123',
            ]);

        $this->assertSame('completed', $result->status);
        $this->assertSame(
            'cpanel-123',
            $result->response['external_id']
        );
        $this->assertNotNull($result->completed_at);
    }

    public function test_it_fails_a_processing_operation(): void
    {
        $operation = ProvisioningOperation::factory()->create([
            'status' => 'processing',
            'attempts' => 1,
        ]);

        $result = app(ProvisioningOperationService::class)
            ->fail($operation, [
                'message' => 'cPanel API unavailable.',
            ]);      
        

        $this->assertSame('failed', $result->status);
        
        $this->assertSame(
            'cPanel API unavailable.',
            $result->errors['message']
        );
        $this->assertNotNull($result->completed_at);
    }

    public function test_it_cancels_a_pending_operation(): void
    {
        $operation = ProvisioningOperation::factory()->create([
            'status' => 'pending',
        ]);

        $result = app(ProvisioningOperationService::class)
            ->cancel($operation);

        $this->assertSame('cancelled', $result->status);
        $this->assertNotNull($result->completed_at);
    }

    public function test_it_cannot_start_a_non_pending_operation(): void
    {
        $operation = ProvisioningOperation::factory()->create([
            'status' => 'completed',
        ]);

        $this->expectException(ValidationException::class);

        app(ProvisioningOperationService::class)
            ->start($operation);
    }

    public function test_it_cannot_complete_a_pending_operation(): void
    {
        $operation = ProvisioningOperation::factory()->create([
            'status' => 'pending',
        ]);

        $this->expectException(ValidationException::class);

        app(ProvisioningOperationService::class)
            ->complete($operation);
    }
}