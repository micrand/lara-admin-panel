<?php

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailAccount extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'service_id',
        'domain_id',
        'hosting_account_id',
        'local_part',
        'email',
        'quota',
        'status',
        'external_id',
        'last_synced_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'quota' => 'integer',
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

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function hostingAccount()
    {
        return $this->belongsTo(HostingAccount::class);
    }

    public function aliases()
    {
        return $this->hasMany(EmailAlias::class);
    }
}