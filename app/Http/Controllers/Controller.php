<?php

namespace App\Http\Controllers;

use App\Models\User;

/**
 * Tum controller'larin ortak atasi.
 *
 * Oturum acmis kullaniciyi bulma ve rol kontrolu yapma islemleri
 * hem RoomController hem MeetingController icinde ayni ayni kopyalanmisti.
 * Bu tekrari onlemek icin ortak metodlar buraya tasindi; alt siniflar
 * bu metodlari miras alarak dogrudan kullanir.
 */
abstract class Controller
{
    /**
     * Oturumdaki kullanici id'sine gore giris yapmis kullaniciyi getirir.
     * Kullanici bulunamazsa null doner (orn. oturum suresi dolmussa).
     */
    protected function currentUser(): ?User
    {
        return User::find(session('user_id'));
    }

    /**
     * Giris yapmis kullanicinin belirtilen role sahip olup olmadigini kontrol eder.
     */
    protected function hasRole(string $role): bool
    {
        $user = $this->currentUser();

        return $user !== null && $user->role === $role;
    }

    /**
     * Kullanici "Ozel Kalem Muduru" mu?
     * Oda/toplanti ekleme-duzenleme-silme yetkisi bu rolde.
     */
    protected function isOzelKalemMuduru(): bool
    {
        return $this->hasRole('ozel_kalem_muduru');
    }

    /**
     * Kullanici "Bakim Sorumlusu" mu?
     * Odalari arizali/arizasiz olarak isaretleme yetkisi bu rolde.
     */
    protected function isBakimSorumlusu(): bool
    {
        return $this->hasRole('bakim_sorumlusu');
    }
    protected function isStajyer(): bool 
    {
         return $this->hasRole('stajyer');
    } 
}
