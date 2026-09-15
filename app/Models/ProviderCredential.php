<?php

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderCredential extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'provider_id',
        'server_id',
        'name',
        'type',
        'encrypted_data',
        'status',
        'expires_at',
        'last_used_at',
    ];

    protected $hidden = [
        'encrypted_data',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}