<?php

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAlias extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'email_account_id',
        'alias',
        'status',
    ];

    public function emailAccount()
    {
        return $this->belongsTo(EmailAccount::class);
    }
}