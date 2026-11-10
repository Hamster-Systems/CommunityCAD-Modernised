<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class SmtpSetting extends Model
{
    protected $table = 'smtp_settings';

    protected $guarded = [];

    protected $hidden = ['password'];

    protected $casts = [
        'enabled' => 'boolean',
        'password' => 'encrypted',
    ];
}
