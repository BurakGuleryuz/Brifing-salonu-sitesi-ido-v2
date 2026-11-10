@extends('layouts.app')

@section('title', 'Yeni Toplantı')

@section('content')
    <h1>Yeni Toplantı Ekle</h1>

    <form action="{{ route('meetings.store') }}" method="POST" class="bg-white p-4 rounded border">
        @csrf

        <div class="mb-3">
            <label class="form-label">Oda</label>
            <select name="room_id" class="form-select" required>
                <option value="">-- Oda Seçin --</option>
                @foreach ($rooms as $room)
                    <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                        {{ $room->name }} @if($room->location) ({{ $room->location }}) @endif
                    </option>
                @endforeach
            </select>
        </div>

        @if ($canCreateBoardMeeting)
            <div class="mb-3">
                <label class="form-label">Toplantı Tipi</label>
                <select name="type" class="form-select" required>
                    <option value="normal" {{ old('type', 'normal') === 'normal' ? 'selected' : '' }}>Normal Toplantı</option>
                    <option value="yonetim_kurulu" {{ old('type') === 'yonetim_kurulu' ? 'selected' : '' }}>Yönetim Kurulu Toplantısı</option>
                </select>
                <div class="form-text">Yönetim Kurulu toplantıları sadece özel kalem müdürüne görünür.</div>
            </div>
        @else
            <input type="hidden" name="type" value="normal">
        @endif

        <div class="mb-3">
            <label class="form-label">Toplantı Başlığı</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Düzenleyen</label>
            <input type="text" name="organizer" class="form-control" value="{{ old('organizer') }}">
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Tarih</label>
                <input type="date" id="meeting_date" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Başlangıç Saati</label>
                <input type="time" id="meeting_start_time" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Süre</label>
                <select id="meeting_duration" class="form-select" required>
                    <option value="30">30 Dakika</option>
                    <option value="60" selected>1 Saat</option>
                    <option value="custom">Uzun Toplantı (Elle Gir)</option>
                </select>
            </div>
        </div>

        <div class="row" id="custom_end_row" style="display: none;">
            <div class="col-md-4 mb-3">
                <label class="form-label">Bitiş Saati</label>
                <input type="time" id="meeting_end_time" class="form-control">
            </div>
        </div>

        <div class="mb-3 p-2 bg-light border rounded">
            <small class="text-muted">
                Başlangıç: <strong id="preview_start">-</strong>
                &nbsp;→&nbsp;
                Bitiş: <strong id="preview_end">-</strong>
            </small>
        </div>
        <div class="mb-3">
    <label class="form-label">Öncelik Durumu</label>
    <select name="priority" class="form-select" required>
        <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
        <option value="acil" {{ old('priority') === 'acil' ? 'selected' : '' }}>🔴 Acil</option>
    </select>
</div>

        <input type="hidden" name="start_time" id="start_time_hidden">
        <input type="hidden" name="end_time" id="end_time_hidden">

        <button type="submit" class="btn btn-primary">Kaydet</button>
        <a href="{{ route('meetings.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
    </form>

    <script>
        const dateInput = document.getElementById('meeting_date');
        const startTimeInput = document.getElementById('meeting_start_time');
        const durationSelect = document.getElementById('meeting_duration');
        const customEndRow = document.getElementById('custom_end_row');
        const customEndInput = document.getElementById('meeting_end_time');
        const startHidden = document.getElementById('start_time_hidden');
        const endHidden = document.getElementById('end_time_hidden');
        const previewStart = document.getElementById('preview_start');
        const previewEnd = document.getElementById('preview_end');

        const today = new Date().toISOString().split('T')[0];
        dateInput.value = today;

        function updateCalculation() {
            const dateVal = dateInput.value;
            const startVal = startTimeInput.value;

            if (!dateVal || !startVal) {
                return;
            }

            const startDateTime = new Date(`${dateVal}T${startVal}`);
            let endDateTime;

            const durationVal = durationSelect.value;

            if (durationVal === 'custom') {
                customEndRow.style.display = 'flex';
                if (!customEndInput.value) {
                    return;
                }
                endDateTime = new Date(`${dateVal}T${customEndInput.value}`);
            } else {
                customEndRow.style.display = 'none';
                const minutes = parseInt(durationVal, 10);
                endDateTime = new Date(startDateTime.getTime() + minutes * 60000);
            }

            const formatForInput = (d) => {
                const pad = (n) => String(n).padStart(2, '0');
                return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
            };

            startHidden.value = formatForInput(startDateTime);
            endHidden.value = formatForInput(endDateTime);

            const formatForDisplay = (d) => {
                const pad = (n) => String(n).padStart(2, '0');
                return `${pad(d.getDate())}.${pad(d.getMonth()+1)}.${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
            };

            previewStart.textContent = formatForDisplay(startDateTime);
            previewEnd.textContent = formatForDisplay(endDateTime);
        }

        dateInput.addEventListener('change', updateCalculation);
        startTimeInput.addEventListener('change', updateCalculation);
        durationSelect.addEventListener('change', updateCalculation);
        customEndInput.addEventListener('change', updateCalculation);
    </script>
@endsection