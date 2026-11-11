<?php

namespace App\Models\Cad;

use Illuminate\Database\Eloquent\Model;

class AddressDirectory extends Model
{
    protected $table = 'cad_address_directory';

    protected $fillable = ['street', 'city', 'postal', 'search_key'];

    public static function keyFor(string $street, ?string $city, ?string $postal): string
    {
        // Deterministic hashed key: avoids indexed string length limitations.
        return hash('sha256', mb_strtolower(trim($street).'|'.trim($city ?? '').'|'.trim($postal ?? '')));
    }
}
