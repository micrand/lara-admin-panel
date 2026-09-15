<?php

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'provider_id',
        'name',
        'hostname',
        'ip',
        'port',
        'status',
        'environment',
        'api_configuration',
        'last_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'api_configuration' => 'array',
            'last_checked_at' => 'datetime',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function credentials()
    {
        return $this->hasMany(ProviderCredential::class);
    }

    public function hostingAccounts()
    {
        return $this->hasMany(HostingAccount::class);
    }

    public function provisioningOperations()
    {
        return $this->hasMany(ProvisioningOperation::class);
    }

    public function syncRuns()
    {
        return $this->hasMany(SyncRun::class);
    }

    public function syncConflicts()
    {
        return $this->hasMany(SyncConflict::class);
    }
}