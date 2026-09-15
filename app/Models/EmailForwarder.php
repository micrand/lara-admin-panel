<?php

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailForwarder extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'domain_id',
        'source',
        'destination',
        'status',
        'external_id',
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}