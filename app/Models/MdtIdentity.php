<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MdtIdentity extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['subdivisions' => 'array'];

    public function userDepartment()
    {
        return $this->belongsTo(UserDepartment::class, 'user_department_id');
    }

    public function civilian()
    {
        return $this->belongsTo(Civilian::class);
    }
}
