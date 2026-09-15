<?php

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'legal_name',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'postal_code',
        'city',
        'state',
        'country_code',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function contacts()
    {
        return $this->hasMany(ClientContact::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}