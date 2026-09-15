<?php

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DnsRecord extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'dns_zone_id',
        'type',
        'name',
        'value',
        'ttl',
        'priority',
        'weight',
        'port',
        'status',
        'external_id',
    ];

    protected function casts(): array
    {
        return [
            'ttl' => 'integer',
            'priority' => 'integer',
            'weight' => 'integer',
            'port' => 'integer',
        ];
    }

    public function dnsZone()
    {
        return $this->belongsTo(DnsZone::class);
    }
}