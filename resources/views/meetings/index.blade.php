@extends('layouts.app')

@section('title', 'Toplantılar')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Toplantılar</h1>
        @if($canCreateMeetings)
        <a href="{{ route('meetings.create') }}" class="btn btn-primary">+ Yeni Toplantı</a>
        @endif
    </div>

    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>Başlık</th>
                <th>Oda</th>
                <th>Düzenleyen</th>
                <th>Başlangıç</th>
                <th>Bitiş</th>
                @if ($canManageMeetings)
                    <th class="text-end">İşlemler</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($meetings as $meeting)
                <tr>
                    <td>
                        {{ $meeting->title }}
                        @if ($meeting->priority === 'acil')
                            <span class="badge bg-danger">🔴 Acil</span>
                        @endif
                        @if ($meeting->type === 'yonetim_kurulu')
                            <span class="badge bg-warning text-dark">Yönetim Kurulu</span>
                        @endif
                    </td>
                    <td>{{ $meeting->room->name }}</td>
                    <td>{{ $meeting->organizer ?? '-' }}</td>
                    <td>{{ $meeting->start_time->format('d.m.Y H:i') }}</td>
                    <td>{{ $meeting->end_time->format('d.m.Y H:i') }}</td>
                    @if ($canManageMeetings)
                        <td class="text-end">
                            <a href="{{ route('meetings.edit', $meeting) }}" class="btn btn-sm btn-outline-secondary">Düzenle</a>
                            <form action="{{ route('meetings.destroy', $meeting) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu toplantıyı silmek istediğinize emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $canManageMeetings ? 6 : 5 }}" class="text-center text-muted">Henüz toplantı eklenmemiş.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        {{ $meetings->links() }}
    </div>
@endsection