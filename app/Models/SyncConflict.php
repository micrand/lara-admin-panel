<?php

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncConflict extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'sync_run_id',
        'server_id',
        'resource_type',
        'resource_id',
        'local_state',
        'remote_state',
        'resolution',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resource_id' => 'string',
            'local_state' => 'array',
            'remote_state' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function syncRun()
    {
        return $this->belongsTo(SyncRun::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}