<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <title>@yield('title') — CMS PUPR</title>
    <link rel="icon" href="{{asset('images/brand/logo-pupr.png')}}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
    <link rel="stylesheet" href="/css/admin-production.css?v=20261123">
</head>
@php
    $role = auth()->user()->role;
    $roleModules = [
        'CONTENT_EDITOR' => ['news','pages','media','agendas','statistics'],
        'SERVICE_EDITOR' => ['services','projects','agendas','media'],
        'TRANSPARENCY_EDITOR' => ['documents','pages','media'],
        'COMPLAINT_OFFICER' => ['complaints'],
    ];
    $allowedModules = in_array($role, ['SUPER_ADMIN','ADMIN'])
        ? ['news','pages','services','projects','documents','agendas','statistics','media','complaints','knowledge','audits','settings']
        : ($roleModules[$role] ?? []);
    $can = fn (string $module) => in_array($module, $allowedModules, true);
@endphp
<body class="admin-body">
<div class="admin-sidebar-backdrop" aria-hidden="true"></div>
<aside class="admin-sidebar" aria-label="Navigasi CMS">
    <a class="admin-logo" href="/admin"><img src="{{asset('images/brand/logo-pupr.png')}}" alt="Logo PUPR"><div><b>CMS PUPR</b><small>Kota Prabumulih</small></div></a>
    <nav>
        <small>UTAMA</small>
        <a @class(['active'=>request()->is('admin')]) href="/admin"><span>⌂</span> Dashboard</a>
        @if(collect(['news','pages','services','projects','documents','agendas','statistics','media'])->contains(fn($module)=>$can($module)))
            <small>KONTEN PUBLIK</small>
            @if($can('news'))<a @class(['active'=>request()->is('admin/news*')]) href="/admin/news"><span>▤</span> Berita</a>@endif
            @if($can('pages'))<a @class(['active'=>request()->is('admin/pages*')]) href="/admin/pages"><span>▧</span> Halaman & Profil</a>@endif
            @if($can('services'))<a @class(['active'=>request()->is('admin/services*')]) href="/admin/services"><span>◇</span> Layanan Publik</a>@endif
            @if($can('projects'))<a @class(['active'=>request()->is('admin/projects*')]) href="/admin/projects"><span>▥</span> Proyek</a>@endif
            @if($can('documents'))<a @class(['active'=>request()->is('admin/documents*')]) href="/admin/documents"><span>▱</span> Dokumen</a>@endif
            @if($can('agendas'))<a @class(['active'=>request()->is('admin/agendas*')]) href="/admin/agendas"><span>◷</span> Agenda</a>@endif
            @if($can('statistics'))<a @class(['active'=>request()->is('admin/statistics*')]) href="/admin/statistics"><span>#</span> Statistik</a>@endif
            @if($can('media'))<a @class(['active'=>request()->is('admin/media*')]) href="/admin/media"><span>▦</span> Foto, Video & PDF</a>@endif
        @endif
        @if($can('complaints') || $can('knowledge'))
            <small>LAYANAN WARGA</small>
            @if($can('complaints'))<a @class(['active'=>request()->is('admin/complaints*')]) href="/admin/complaints"><span>◎</span> Pengaduan</a>@endif
            @if($can('knowledge'))<a @class(['active'=>request()->is('admin/knowledge*')]) href="/admin/knowledge"><span>✦</span> Basis Pengetahuan</a>@endif
        @endif
        @if(in_array($role,['SUPER_ADMIN','ADMIN']))
            <small>SISTEM</small>
            <a @class(['active'=>request()->is('admin/settings')]) href="/admin/settings"><span>⚙</span> Pengaturan Portal</a>
            <a @class(['active'=>request()->is('admin/audits*')]) href="/admin/audits"><span>≡</span> Audit Aktivitas</a>
        @endif
        @if($role==='SUPER_ADMIN')<a @class(['active'=>request()->is('admin/users*')]) href="/admin/users"><span>♙</span> Pengguna & Role</a>@endif
        <small>WEBSITE</small><a href="/" target="_blank" rel="noopener"><span>↗</span> Lihat Website</a>
    </nav>
    <div class="admin-user"><span>{{strtoupper(substr(auth()->user()->name,0,2))}}</span><div><b>{{auth()->user()->name}}</b><small>{{str_replace('_',' ',auth()->user()->role)}}</small></div><form method="post" action="/admin/logout">@csrf<button title="Keluar" aria-label="Keluar">↪</button></form></div>
</aside>
<div class="admin-main">
    <header class="admin-top"><button class="admin-menu" type="button" aria-label="Buka menu" aria-expanded="false">☰</button><div><small>DINAS PUPR KOTA PRABUMULIH</small><b>@yield('header','Dashboard')</b></div><span class="production-pill">CMS AKTIF</span></header>
    <main>
        @if(session('success'))<div class="flash" role="status">✓ {{session('success')}}</div>@endif
        @if(session('warning'))<div class="flash flash-warning" role="alert">{{session('warning')}}</div>@endif
        @if($errors->any())<div class="form-errors admin-global-errors" role="alert"><b>Data belum dapat diproses.</b><ul>@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div>
</body>
</html>
