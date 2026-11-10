<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Oda (brifing salonu) CRUD islemleri + ariza isaretleme.
 *
 * Yetki kurali:
 * - Oda ekleme/duzenleme/silme  -> sadece Ozel Kalem Muduru
 * - Ariza isaretleme/kaldirma   -> sadece Bakim Sorumlusu
 * - Listeleme                  -> her giris yapmis kullanici
 *
 * currentUser(), isOzelKalemMuduru(), isBakimSorumlusu() metodlari
 * taban Controller sinifindan miras alinir (bkz. app/Http/Controllers/Controller.php).
 */
class RoomController extends Controller
{
    /**
     * Oda ekleme/duzenleme/silme yetkisi sadece Ozel Kalem Muduru'nde.
     * Personel sadece goruntuleyebilir.
     */
    private function canManageRooms(): bool
    {
        return $this->isOzelKalemMuduru();
    }

    /**
     * Oda formundaki (ekleme/duzenleme) dogrulama kurallari.
     * Ayni kurallar hem store() hem update() icinde kullanildigi icin
     * tek bir yerden yonetiliyor.
     */
    private function roomValidationRules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Tum odalari isme gore siralayip listeler.
     * Butonlarin (Duzenle/Sil/Ariza Bildir) goruntulenip goruntulenmeyecegini
     * View'a "canManageFaults" ve "canManageRooms" bayraklariyla bildirir.
     */
    public function index(): View
    {
        $rooms = Room::orderBy('name')->get();
        $canManageFaults = $this->isBakimSorumlusu();
        $canManageRooms = $this->canManageRooms();

        return view('rooms.index', compact('rooms', 'canManageFaults', 'canManageRooms'));
    }

    /**
     * Yeni oda ekleme formunu gosterir. Sadece yetkili kullanici gorebilir.
     */
    public function create(): View
    {
        if (! $this->canManageRooms()) {
            abort(403, 'Oda ekleme yetkiniz yok.');
        }

        return view('rooms.create');
    }

    /**
     * Formdan gelen veriyi dogrulayip yeni oda kaydi olusturur.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $this->canManageRooms()) {
            abort(403, 'Oda ekleme yetkiniz yok.');
        }

        $validated = $request->validate($this->roomValidationRules());
        $validated['is_active'] = $request->has('is_active');

        Room::create($validated);

        return redirect()->route('rooms.index')->with('success', 'Oda başarıyla eklendi.');
    }

    /**
     * Var olan bir odanin duzenleme formunu gosterir.
     */
    public function edit(Room $room): View
    {
        if (! $this->canManageRooms()) {
            abort(403, 'Oda düzenleme yetkiniz yok.');
        }

        return view('rooms.edit', compact('room'));
    }

    /**
     * Duzenleme formundan gelen veriyi dogrulayip odayi gunceller.
     */
    public function update(Request $request, Room $room): RedirectResponse
    {
        if (! $this->canManageRooms()) {
            abort(403, 'Oda düzenleme yetkiniz yok.');
        }

        $validated = $request->validate($this->roomValidationRules());
        $validated['is_active'] = $request->has('is_active');

        $room->update($validated);

        return redirect()->route('rooms.index')->with('success', 'Oda güncellendi.');
    }

    /**
     * Odayi veritabanindan siler.
     */
    public function destroy(Room $room): RedirectResponse
    {
        if (! $this->canManageRooms()) {
            abort(403, 'Oda silme yetkiniz yok.');
        }

        $room->delete();
        return redirect()->route('rooms.index')->with('success', 'Oda silindi.');
    }

    /**
     * Odayi "arizali" olarak isaretler (ariza notu isteniyorsa birlikte kaydeder).
     * Sadece Bakim Sorumlusu kullanabilir.
     */
    public function markFaulty(Request $request, Room $room): RedirectResponse
    {
        if (! $this->isBakimSorumlusu()) {
            abort(403, 'Bu işlem için bakım sorumlusu yetkisi gerekiyor.');
        }

        $validated = $request->validate([
            'fault_note' => 'nullable|string|max:255',
        ]);

        $room->update([
            'is_faulty'  => true,
            'fault_note' => $validated['fault_note'] ?? null,
        ]);

        return redirect()->route('rooms.index')->with('success', $room->name . ' arızalı olarak işaretlendi.');
    }

    /**
     * Odanin ariza durumunu kaldirir, ariza notunu temizler.
     */
    public function clearFaulty(Room $room): RedirectResponse
    {
        if (! $this->isBakimSorumlusu()) {
            abort(403, 'Bu işlem için bakım sorumlusu yetkisi gerekiyor.');
        }

        $room->update([
            'is_faulty'  => false,
            'fault_note' => null,
        ]);

        return redirect()->route('rooms.index')->with('success', $room->name . ' için arıza durumu kaldırıldı.');
    }
}