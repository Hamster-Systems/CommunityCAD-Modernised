<?php

namespace App\Models;

use App\Models\Cad\ActiveUnit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $incrementing = false;

    protected $guarded = [];

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'member_join_date' => 'datetime',
        'birthday' => 'date',
        'reapply_date' => 'date',
    ];

    public function localCredential()
    {
        return $this->hasOne(LocalAccountCredential::class);
    }

    public function applications()
    {
        return $this->hasMany('App\Models\Application');
    }

    public function active_unit()
    {
        return $this->hasOne(ActiveUnit::class);
    }

    public function histories()
    {
        return $this->hasMany('App\Models\History');
    }

    public static function dec2hex($number)
    {
        $hexvalues = [
            '0', '1', '2', '3', '4', '5', '6', '7',
            '8', '9', 'A', 'B', 'C', 'D', 'E', 'F',
        ];
        $hexval = '';
        while ($number != '0') {
            $hexval = $hexvalues[bcmod($number, '16', 0)].$hexval;
            $number = bcdiv($number, '16', 0);
        }

        return $hexval;
    }

    public function getAgeAttribute()
    {
        // Local accounts can be created before a date of birth is collected.
        // Never interpret a missing birthday as today's date or a valid age.
        return $this->birthday?->age;
    }

    public function getPreferredNameAttribute()
    {
        if ($this->display_name) {
            return $this->display_name;
        }

        return $this->localCredential?->username
            ?? $this->discord_name
            ?? 'CommunityCAD user';
    }

    public function getDiscordAttribute()
    {
        // Discord is an optional linked provider, not the account identity.
        if (!$this->discord_name) {
            return null;
        }

        return $this->discord_name.($this->discriminator ? '#'.$this->discriminator : '');
    }

    public function getStatusNameAttribute()
    {
        switch ($this->account_status) {
            case 1:
                return 'User';
                break;
            case 2:
                return 'Applicant';
                break;
            case 3:
                return 'Member';
                break;
            case 4:
                return 'Suspended/LOA';
                break;
            case 5:
                return 'Temporary Ban';
                break;
            case 6:
                return 'Permanent Ban';
                break;
        }
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function civilian_level()
    {
        return $this->hasOne(CivilianLevel::class, 'id', 'civilian_level_id');
    }
}
