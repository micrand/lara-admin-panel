<?php

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'service_id',
        'hosting_account_id',
        'parent_domain_id',
        'name',
        'fqdn',
        'type',
        'status',
        'expires_at',
        'external_id',
        'last_synced_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function hostingAccount()
    {
        return $this->belongsTo(HostingAccount::class);
    }

    public function parentDomain()
    {
        return $this->belongsTo(Domain::class, 'parent_domain_id');
    }

    public function childDomains()
    {
        return $this->hasMany(Domain::class, 'parent_domain_id');
    }

    public function aliases()
    {
        return $this->hasMany(DomainAlias::class);
    }

    public function dnsZone()
    {
        return $this->hasOne(DnsZone::class);
    }

    public function emailAccounts()
    {
        return $this->hasMany(EmailAccount::class);
    }

    public function emailForwarders()
    {
        return $this->hasMany(EmailForwarder::class);
    }
}