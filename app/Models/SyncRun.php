<?php

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncRun extends Model
{
    use HasFactory;
    use HasUuid;

    const UPDATED_AT = null;

    protected $fillable = [
        'server_id',
        'type',
        'status',
        'started_at',
        'completed_at',
        'items_processed',
        'items_created',
        'items_updated',
        'items_deleted',
        'items_failed',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'items_processed' => 'integer',
            'items_created' => 'integer',
            'items_updated' => 'integer',
            'items_deleted' => 'integer',
            'items_failed' => 'integer',
        ];
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function conflicts()
    {
        return $this->hasMany(SyncConflict::class);
    }
}