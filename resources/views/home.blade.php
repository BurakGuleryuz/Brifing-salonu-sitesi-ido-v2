{{-- resources/views/home.blade.php --}}
{{-- Giris sonrasi gosterilen karsilama ekrani. --}}
@extends('layouts.app')

@section('title', 'Ana Sayfa')

@section('content')

    <div class="p-4 p-md-5 rounded-3 mb-4" style="background: linear-gradient(135deg, var(--ido-navy) 0%, var(--ido-blue) 100%); color: #fff;">
        <h1 class="text-white mb-2">Hoş geldiniz, {{ $user->name }} 👋</h1>
        <p class="mb-0" style="opacity: 0.9;">
            Brifing Salonu Toplantı Yönetim Sistemi'ne giriş yaptınız.
            Aşağıdan odaları görüntüleyebilir veya toplantıları yönetebilirsiniz.
        </p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Aktif Oda Sayısı</div>
                    <div class="display-6 fw-bold" style="color: var(--ido-navy);">{{ $aktifOdaSayisi }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Bugünkü Toplantı Sayısı</div>
                    <div class="display-6 fw-bold" style="color: var(--ido-navy);">{{ $bugunkuToplantiSayisi }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <a href="{{ route('rooms.index') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div style="font-size: 2rem;">🏢</div>
                        <div>
                            <h5 class="mb-1">Odalar</h5>
                            <div class="text-muted small">Brifing salonlarını görüntüle, arıza durumu takip et</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('meetings.index') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div style="font-size: 2rem;">📅</div>
                        <div>
                            <h5 class="mb-1">Toplantılar</h5>
                            <div class="text-muted small">Toplantıları listele, yeni toplantı oluştur</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

@endsection
