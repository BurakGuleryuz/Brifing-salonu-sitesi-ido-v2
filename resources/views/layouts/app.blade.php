@php
    $authUser = \App\Models\User::find(session('user_id'));
    $successMsg = session('success');
    $isWelcomeMsg = $successMsg && str_starts_with($successMsg, 'Hoş geldiniz');
@endphp
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Toplantı Yönetim Sistemi')</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/ido-logo.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --ido-navy: #003B5C;
            --ido-blue: #0072BC;
            --ido-orange: #F7941D;
            --ido-light: #F4F7FA;
            --ido-text: #1A2B3C;
        }

        body {
            background-color: var(--ido-light);
            color: var(--ido-text);
        }

        .navbar.bg-dark {
            background: linear-gradient(135deg, var(--ido-navy) 0%, #00527D 100%) !important;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .wave-divider {
            width: 100%;
            overflow: hidden;
            line-height: 0;
            margin-bottom: 1.75rem;
            margin-top: -1px;
        }
        .wave-divider svg {
            width: 200%;
            height: 60px;
            display: block;
        }
        .wave-layer-1 { animation: wave-scroll 26s linear infinite; }
        .wave-layer-2 { animation: wave-scroll 20s linear infinite; }
        .wave-layer-3 { animation: wave-scroll 15s linear infinite; }
        .wave-layer-4 { animation: wave-scroll 11s linear infinite; }

        @keyframes wave-scroll {
            from { transform: translateX(0); }
            to   { transform: translateX(-1200px); }
        }

        .btn-primary {
            background-color: var(--ido-blue);
            border-color: var(--ido-blue);
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--ido-navy);
            border-color: var(--ido-navy);
        }

        .btn-outline-info {
            color: var(--ido-orange);
            border-color: var(--ido-orange);
        }
        .btn-outline-info:hover {
            background-color: var(--ido-orange);
            border-color: var(--ido-orange);
            color: #fff;
        }

        .btn-outline-secondary {
            color: var(--ido-navy);
            border-color: var(--ido-navy);
        }
        .btn-outline-secondary:hover {
            background-color: var(--ido-navy);
            border-color: var(--ido-navy);
        }

        a { color: var(--ido-blue); }
        a:hover { color: var(--ido-navy); }

        h1, h2, h3 { color: var(--ido-navy); }

        .card, .border { border-color: #dde5ec !important; }

        .table thead tr th,
        table thead tr th {
            background-color: var(--ido-navy) !important;
            color: #fff !important;
            border-color: var(--ido-navy) !important;
        }

        .welcome-alert {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .welcome-icon {
            width: 34px;
            height: 34px;
            flex-shrink: 0;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                <img src="{{ asset('images/ido-logo.jpg') }}" alt="İDO" style="height:36px; width:auto; border-radius:4px;">
                <span>Toplantı Yönetim Sistemi</span>
            </a>
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="{{ route('rooms.index') }}">Odalar</a>
                <a class="nav-link" href="{{ route('meetings.index') }}">Toplantılar</a>
            </div>
            @if ($authUser)
                <div class="d-flex align-items-center">
                    <span class="text-white me-3">
                        {{ $authUser->name }}
                        @if ($authUser->role === 'ozel_kalem_muduru')
                            <span class="badge bg-warning text-dark ms-1">Özel Kalem Müdürü</span>
                        @elseif ($authUser->role === 'bakim_sorumlusu')
                            <span class="badge ms-1" style="background-color:#F7941D;">Bakım Sorumlusu</span>
                        @elseif ($authUser->role === 'stajyer')
                            <span class="badge bg-secondary ms-1">Stajyer</span>
                        @endif
                    </span>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light">Çıkış</button>
                    </form>
                </div>
            @endif
        </div>
    </nav>

    <div class="wave-divider">
        <svg viewBox="0 0 2400 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <g class="wave-layer-1">
                <path d="M0,12 C100,4 200,20 300,12 C400,4 500,20 600,12 C700,4 800,20 900,12 C1000,4 1100,20 1200,12 L1200,60 L0,60 Z" fill="#003B5C"></path>
                <path d="M0,12 C100,4 200,20 300,12 C400,4 500,20 600,12 C700,4 800,20 900,12 C1000,4 1100,20 1200,12 L1200,60 L0,60 Z" fill="#003B5C" transform="translate(1200,0)"></path>
            </g>
            <g class="wave-layer-2">
                <path d="M0,22 C100,14 200,30 300,22 C400,14 500,30 600,22 C700,14 800,30 900,22 C1000,14 1100,30 1200,22 L1200,60 L0,60 Z" fill="#0072BC"></path>
                <path d="M0,22 C100,14 200,30 300,22 C400,14 500,30 600,22 C700,14 800,30 900,22 C1000,14 1100,30 1200,22 L1200,60 L0,60 Z" fill="#0072BC" transform="translate(1200,0)"></path>
            </g>
            <g class="wave-layer-3">
                <path d="M0,34 C100,26 200,42 300,34 C400,26 500,42 600,34 C700,26 800,42 900,34 C1000,26 1100,42 1200,34 L1200,60 L0,60 Z" fill="#6FB8E6"></path>
                <path d="M0,34 C100,26 200,42 300,34 C400,26 500,42 600,34 C700,26 800,42 900,34 C1000,26 1100,42 1200,34 L1200,60 L0,60 Z" fill="#6FB8E6" transform="translate(1200,0)"></path>
            </g>
            <g class="wave-layer-4">
                <path d="M0,46 C100,38 200,54 300,46 C400,38 500,54 600,46 C700,38 800,54 900,46 C1000,38 1100,54 1200,46 L1200,60 L0,60 Z" fill="#C9EAFB"></path>
                <path d="M0,46 C100,38 200,54 300,46 C400,38 500,54 600,46 C700,38 800,54 900,46 C1000,38 1100,54 1200,46 L1200,60 L0,60 Z" fill="#C9EAFB" transform="translate(1200,0)"></path>
            </g>
        </svg>
    </div>

    <div class="container">

        @if ($successMsg)
            <div class="alert alert-success welcome-alert">
                @if ($isWelcomeMsg)
                    <svg class="welcome-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 62
                                 C 18 40, 32 22, 52 20
                                 C 68 18.5, 80 28, 84 40
                                 C 76 37, 68 39, 64 45
                                 C 72 45, 79 50, 83 58
                                 C 73 55, 65 57, 61 62
                                 C 68 64, 74 70, 76 78
                                 C 62 74, 52 76, 46 68
                                 C 36 74, 24 73, 16 66
                                 C 22 64, 28 61, 32 57
                                 C 24 58, 17 60, 12 62 Z"
                              fill="#0072BC"></path>
                        <circle cx="58" cy="32" r="2.6" fill="#003B5C"></circle>
                    </svg>
                @endif
                <span>{{ $successMsg }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>