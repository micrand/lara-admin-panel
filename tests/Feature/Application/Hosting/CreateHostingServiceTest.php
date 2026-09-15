<?php

namespace Tests\Feature\Application\Hosting;

use App\Application\Hosting\Actions\CreateHostingService;
use App\Application\Hosting\DTOs\CreateHostingServiceData;
use App\Models\Client;
use App\Models\Service;
use App\Models\ServiceType;
use Database\Seeders\ServiceTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateHostingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ServiceTypeSeeder::class);
    }

    public function test_it_creates_a_pending_hosting_service(): void
    {
        $client = Client::factory()->create([
            'name' => 'Hosting Test Company',
        ]);

        $data = new CreateHostingServiceData(
            clientId: $client->id,
            name: 'Professional Web Hosting',
            startsAt: now()->toDateTimeString(),
            expiresAt: now()->addYear()->toDateTimeString(),
            metadata: [
                'source' => 'test',
            ],
        );

        $service = app(CreateHostingService::class)->execute($data);

        $this->assertInstanceOf(Service::class, $service);

        $this->assertSame($client->id, $service->client_id);
        $this->assertSame('hosting', $service->serviceType->slug);
        $this->assertSame('pending', $service->status);

        $this->assertStringStartsWith(
            'HOST-',
            $service->reference
        );

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'client_id' => $client->id,
            'service_type_id' => $service->service_type_id,
            'name' => 'Professional Web Hosting',
            'status' => 'pending',
        ]);
    }

    public function test_it_only_uses_the_hosting_service_type(): void
    {
        $client = Client::factory()->create();

        $data = new CreateHostingServiceData(
            clientId: $client->id,
            name: 'Web Hosting',
        );

        $service = app(CreateHostingService::class)->execute($data);

        $hostingType = ServiceType::where(
            'slug',
            'hosting'
        )->firstOrFail();

        $this->assertSame(
            $hostingType->id,
            $service->service_type_id
        );
    }

    public function test_it_fails_when_client_does_not_exist(): void
    {
        $data = new CreateHostingServiceData(
            clientId: '01900000-0000-7000-8000-000000000000',
            name: 'Web Hosting',
        );

        $this->expectException(
            \Illuminate\Database\Eloquent\ModelNotFoundException::class
        );

        app(CreateHostingService::class)->execute($data);
    }
}