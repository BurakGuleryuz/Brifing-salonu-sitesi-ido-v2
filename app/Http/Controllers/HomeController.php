<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Meeting;
use Illuminate\View\View;
use Carbon\Carbon;

/**
 * Giris sonrasi gosterilen karsilama (ana sayfa) ekrani.
 *
 * Once dogrudan Odalar listesine yonlendiriliyordu; simdi kullaniciyi
 * once bu ozet sayfada karsiliyoruz, oradan Odalar/Toplantilar'a gecilebiliyor.
 */
class HomeController extends Controller
{
    public function index(): View
    {
        $user = $this->currentUser();

        // Karsilama ekraninda gosterilecek birkac basit sayi.
        $aktifOdaSayisi = Room::where('is_active', true)->count();
        $bugunkuToplantiSayisi = Meeting::whereDate('start_time', Carbon::today())->count();

        return view('home', [
            'user' => $user,
            'aktifOdaSayisi' => $aktifOdaSayisi,
            'bugunkuToplantiSayisi' => $bugunkuToplantiSayisi,
        ]);
    }
}
