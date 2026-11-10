<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;

/**
 * Toplanti CRUD islemleri + oda cakisma kontrolu.
 *
 * Yetki kurali:
 * - Toplanti ekleme            -> her giris yapmis kullanici (Yonetim Kurulu turu haric)
 * - Yonetim Kurulu toplantisi  -> sadece Ozel Kalem Muduru olusturabilir
 * - Duzenleme/silme            -> sadece Ozel Kalem Muduru
 *
 * currentUser() ve isOzelKalemMuduru() taban Controller sinifindan miras alinir
 * (bkz. app/Http/Controllers/Controller.php) — RoomController ile burada tekrar edilmiyor.
 */
class MeetingController extends Controller
{
    /**
     * Toplantıları düzenleme/silme yetkisi sadece Özel Kalem Müdürü'nde.
     * Normal personel toplantıları görebilir ama üzerinde değişiklik yapamaz.
     */
    private function canManageMeetings(): bool
    {
        return $this->isOzelKalemMuduru();
    }

    /**
     * Toplanti formundaki (ekleme/duzenleme) dogrulama kurallari.
     * store() ve update() ayni kurallari kullandigi icin tek yerden yonetiliyor.
     */
    private function meetingValidationRules(): array
    {
        return [
            'room_id'    => 'required|exists:rooms,id',
            'type'       => 'required|in:normal,yonetim_kurulu',
            'priority'   => 'required|in:normal,acil',
            'title'      => 'required|string|max:255',
            'organizer'  => 'nullable|string|max:255',
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after:start_time',
        ];
    }

    /**
     * Secilen oda + saat araligi baska bir toplantiyla cakisiyor mu diye kontrol eder.
     * $excludeMeetingId, duzenleme sirasinda toplantinin kendisiyle cakismis
     * gibi gorunmemesi icin disarida birakilir.
     */
    private function hasSchedulingConflict(array $data, ?int $excludeMeetingId = null): bool
    {
        $start = Carbon::parse($data['start_time']);
        $end   = Carbon::parse($data['end_time']);

        return Meeting::overlapping($data['room_id'], $start, $end, $excludeMeetingId)->exists();
    }

    /**
     * Toplantilari en yeni tarihten eskiye dogru listeler.
     * Ozel Kalem Muduru disindaki kullanicilar Yonetim Kurulu toplantilarini goremez.
     */
   public function index(): View
        {
        $query = Meeting::with('room')->orderBy('start_time', 'desc');

        if (! $this->isOzelKalemMuduru()) {
        $query->where('type', '!=', 'yonetim_kurulu');
        }

        $meetings = $query->paginate(15);
        $canManageMeetings = $this->canManageMeetings();
        $canCreateMeetings = ! $this->isStajyer();

        return view('meetings.index', compact('meetings', 'canManageMeetings', 'canCreateMeetings'));
        }
    /**
     * Yeni toplanti formunu gosterir. Sadece aktif (arizasiz olmayan degil, "is_active") odalar listelenir.
     */
    public function create(): View
    {
        if ($this->isStajyer()) 
        {
          abort(403, 'Stajyer rolü toplantı oluşturamaz.');
        }
        $rooms = Room::where('is_active', true)->orderBy('name')->get();
        $canCreateBoardMeeting = $this->isOzelKalemMuduru();

        return view('meetings.create', compact('rooms', 'canCreateBoardMeeting'));
    }

    /**
     * Formdan gelen veriyi dogrular, oda cakismasi yoksa yeni toplanti olusturur.
     */
    public function store(Request $request): RedirectResponse
    {
         if ($this->isStajyer())
            {
                abort(403, 'Stajyer rolü toplantı oluşturamaz.');
            } 
        $validated = $request->validate($this->meetingValidationRules());

        if ($validated['type'] === 'yonetim_kurulu' && ! $this->isOzelKalemMuduru()) {
            abort(403, 'Yönetim Kurulu toplantısı oluşturma yetkiniz yok.');
        }

        if ($this->hasSchedulingConflict($validated)) {
            return back()
                ->withInput()
                ->withErrors(['start_time' => 'Bu oda, seçilen saat aralığında zaten dolu. Lütfen başka bir saat veya oda seçin.']);
        }

        Meeting::create($validated);

        return redirect()->route('meetings.index')->with('success', 'Toplantı başarıyla oluşturuldu.');
    }

    /**
     * Var olan bir toplantinin duzenleme formunu gosterir.
     */
    public function edit(Meeting $meeting): View
    {
        // Düzenleme yetkisi sadece Özel Kalem Müdürü'nde
        if (! $this->canManageMeetings()) {
            abort(403, 'Toplantı düzenleme yetkiniz yok.');
        }

        $rooms = Room::where('is_active', true)->orderBy('name')->get();
        $canCreateBoardMeeting = $this->isOzelKalemMuduru();

        return view('meetings.edit', compact('meeting', 'rooms', 'canCreateBoardMeeting'));
    }

    /**
     * Duzenleme formundan gelen veriyi dogrular, oda cakismasi yoksa toplantiyi gunceller.
     * $meeting->id, cakisma kontrolunde toplantinin kendisiyle kiyaslanmamasi icin disarida tutulur.
     */
    public function update(Request $request, Meeting $meeting): RedirectResponse
    {
        if (! $this->canManageMeetings()) {
            abort(403, 'Toplantı düzenleme yetkiniz yok.');
        }

        $validated = $request->validate($this->meetingValidationRules());

        if ($validated['type'] === 'yonetim_kurulu' && ! $this->isOzelKalemMuduru()) {
            abort(403, 'Yönetim Kurulu toplantısı oluşturma yetkiniz yok.');
        }

        if ($this->hasSchedulingConflict($validated, $meeting->id)) {
            return back()
                ->withInput()
                ->withErrors(['start_time' => 'Bu oda, seçilen saat aralığında zaten dolu. Lütfen başka bir saat veya oda seçin.']);
        }

        $meeting->update($validated);

        return redirect()->route('meetings.index')->with('success', 'Toplantı güncellendi.');
    }

    /**
     * Toplantiyi veritabanindan siler.
     */
    public function destroy(Meeting $meeting): RedirectResponse
    {
        if (! $this->canManageMeetings()) {
            abort(403, 'Toplantı silme yetkiniz yok.');
        }

        $meeting->delete();
        return redirect()->route('meetings.index')->with('success', 'Toplantı silindi.');
    }
}