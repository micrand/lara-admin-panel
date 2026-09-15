<?php

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'status',
        'capabilities',
        'configuration',
    ];

    protected function casts(): array
    {
        return [
            'capabilities' => 'array',
            'configuration' => 'array',
        ];
    }

    public function servers()
    {
        return $this->hasMany(Server::class);
    }

    public function credentials()
    {
        return $this->hasMany(ProviderCredential::class);
    }
}