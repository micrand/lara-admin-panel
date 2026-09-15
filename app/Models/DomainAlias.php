<?php

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomainAlias extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'domain_id',
        'alias_domain_id',
        'status',
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function aliasDomain()
    {
        return $this->belongsTo(Domain::class, 'alias_domain_id');
    }
}