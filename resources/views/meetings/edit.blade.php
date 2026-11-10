@extends('layouts.app')

@section('title', 'Toplantıyı Düzenle')

@section('content')
    <h1>Toplantıyı Düzenle</h1>

    <form action="{{ route('meetings.update', $meeting) }}" method="POST" class="bg-white p-4 rounded border">
        @csrf
        @method('PUT')
       
        @if ($canCreateBoardMeeting)
    <div class="mb-3">
        <label class="form-label">Toplantı Tipi</label>
        <select name="type" class="form-select" required>
            <option value="normal" {{ old('type', $meeting->type) === 'normal' ? 'selected' : '' }}>Normal Toplantı</option>
            <option value="yonetim_kurulu" {{ old('type', $meeting->type) === 'yonetim_kurulu' ? 'selected' : '' }}>Yönetim Kurulu Toplantısı</option>
        </select>
    </div>
    <div class="mb-3">
    <label class="form-label">Öncelik Durumu</label>
    <select name="priority" class="form-select" required>
        <option value="normal" {{ old('priority', $meeting->priority) === 'normal' ? 'selected' : '' }}>Normal</option>
        <option value="acil" {{ old('priority', $meeting->priority) === 'acil' ? 'selected' : '' }}>🔴 Acil</option>
    </select>
</div>
@else
    <input type="hidden" name="type" value="{{ $meeting->type }}">
@endif

        <div class="mb-3">
            <label class="form-label">Oda</label>
            <select name="room_id" class="form-select" required>
                @foreach ($rooms as $room)
                    <option value="{{ $room->id }}" {{ old('room_id', $meeting->room_id) == $room->id ? 'selected' : '' }}>
                        {{ $room->name }} @if($room->location) ({{ $room->location }}) @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Toplantı Başlığı</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $meeting->title) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Düzenleyen</label>
            <input type="text" name="organizer" class="form-control" value="{{ old('organizer', $meeting->organizer) }}">
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Başlangıç Saati</label>
                <input type="datetime-local" name="start_time" class="form-control"
                       value="{{ old('start_time', $meeting->start_time->format('Y-m-d\TH:i')) }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Bitiş Saati</label>
                <input type="datetime-local" name="end_time" class="form-control"
                       value="{{ old('end_time', $meeting->end_time->format('Y-m-d\TH:i')) }}" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Güncelle</button>
        <a href="{{ route('meetings.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
    </form>
@endsection