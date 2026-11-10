<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Brifing salonu (oda) modeli.
 *
 * Alanlar:
 * - name        : Oda adi (orn. "A Blok Brifing Salonu")
 * - location     : Odanin bulundugu yer/kat bilgisi
 * - capacity     : Kisi kapasitesi
 * - is_active    : Oda kullanima acik mi? (kapali odalar toplanti formunda gorunmez)
 * - is_faulty    : Oda su an arizali mi? (bakim sorumlusu tarafindan isaretlenir)
 * - fault_note   : Ariza aciklamasi (istege bagli)
 */
class Room extends Model
{
    protected $fillable = [
        'name',
        'location',
        'capacity',
        'is_active',
        'is_faulty',
        'fault_note',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_faulty' => 'boolean',
    ];

    /**
     * Bu odaya ait tum toplantilar (bir odanin birden fazla toplantisi olabilir).
     */
    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }
}
