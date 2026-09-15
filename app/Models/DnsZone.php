<?php

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DnsZone extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'domain_id',
        'name',
        'status',
        'serial',
        'provider_zone_id',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'serial' => 'integer',
            'last_synced_at' => 'datetime',
        ];
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function records()
    {
        return $this->hasMany(DnsRecord::class);
    }
}