<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalAccountCredential extends Model
{
    protected $guarded = [];

    protected $hidden = ['password'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
