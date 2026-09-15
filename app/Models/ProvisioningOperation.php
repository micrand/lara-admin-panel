<?php

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvisioningOperation extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'client_id',
        'server_id',
        'service_id',
        'operation',
        'resource_type',
        'resource_id',
        'status',
        'attempts',
        'idempotency_key',
        'request_payload',
        'response_payload',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'resource_id' => 'string',
            'attempts' => 'integer',
            'request_payload' => 'array',
            'response_payload' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}