<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;

class CadAudioEvent extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['created_at' => 'datetime'];
}
