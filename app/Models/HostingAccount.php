<?php

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostingAccount extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'service_id',
        'server_id',
        'provider_account_id',
        'username',
        'primary_domain',
        'external_id',
        'status',
        'last_synced_at',
        'metadata',
    ];

    protected $hidden = [
        'provider_account_id',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function emailAccounts()
    {
        return $this->hasMany(EmailAccount::class);
    }
}