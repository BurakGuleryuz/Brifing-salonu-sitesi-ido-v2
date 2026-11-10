<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * Toplanti modeli.
 *
 * Alanlar:
 * - room_id     : Toplantinin yapilacagi oda
 * - type        : "normal" veya "yonetim_kurulu"
 * - priority    : "normal" veya "acil" (acil toplantilar tum kullanicilara e-posta ile bildirilir)
 * - title       : Toplanti basligi
 * - organizer   : Toplantiyi duzenleyen kisi (istege bagli)
 * - start_time  : Baslangic zamani
 * - end_time    : Bitis zamani
 */
class Meeting extends Model
{
    protected $fillable = [
        'room_id',
        'type',
        'priority',
        'title',
        'organizer',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    /**
     * Bu toplantinin yapilacagi oda.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Verilen oda + saat araligiyla cakisan toplantilari bulan sorgu ("scope").
     * MeetingController icinde hem yeni toplanti eklerken hem duzenlerken
     * "bu oda o saatte zaten dolu mu?" kontrolu icin kullanilir.
     *
     * $excludeMeetingId verilirse (duzenleme durumunda), toplanti kendisiyle
     * cakisiyormus gibi gorunmesin diye sonuc disinda birakilir.
     */
    public function scopeOverlapping(Builder $query, int $roomId, Carbon $start, Carbon $end, ?int $excludeMeetingId = null): Builder
    {
        $query->where('room_id', $roomId)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);

        // Duzenleme yapiliyorsa, toplanti kendisiyle karsilastirilmasin diye disarida birakiliyor.
        if ($excludeMeetingId !== null) {
            $query->where('id', '!=', $excludeMeetingId);
        }

        return $query;
    }
}
