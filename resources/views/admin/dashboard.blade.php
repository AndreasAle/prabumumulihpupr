@extends('layouts.admin')
@section('title','Dashboard')
@section('header','Ringkasan Website')
@section('content')
@php($role=auth()->user()->role)
<div class="admin-welcome"><div><span class="kicker">{{now()->translatedFormat('l, d F Y')}}</span><h1>Selamat datang, {{explode(' ',auth()->user()->name)[0]}}.</h1><p>Pantau konten, layanan warga, media, dan aktivitas pengelola portal.</p></div><div class="quick-actions">
@if(in_array($role,['SUPER_ADMIN','ADMIN','CONTENT_EDITOR']))<a href="/admin/news/create">+ Berita</a><a href="/admin/pages/create">+ Halaman</a>@endif
@if(in_array($role,['SUPER_ADMIN','ADMIN','SERVICE_EDITOR']))<a href="/admin/services/create">+ Layanan</a>@endif
@if(in_array($role,['SUPER_ADMIN','ADMIN','CONTENT_EDITOR','SERVICE_EDITOR','TRANSPARENCY_EDITOR']))<a href="/admin/media/create">+ Media</a>@endif
</div></div>
<div class="metric-grid">@foreach($counts as $label=>$count)<div><small>{{$label}}</small><strong>{{$count}}</strong><span>Data aktual</span></div>@endforeach</div>
<div @class(['admin-grid','admin-grid-single'=>!in_array($role,['SUPER_ADMIN','ADMIN','COMPLAINT_OFFICER'])])>
@if(in_array($role,['SUPER_ADMIN','ADMIN','COMPLAINT_OFFICER']))<section class="admin-panel"><header><div><small>LAPORAN MASUK</small><h2>Pengaduan terbaru</h2></div><a href="/admin/complaints">Lihat semua →</a></header><div class="complaint-table">@forelse($complaints as $complaint)<a href="/admin/complaints/{{$complaint->id}}/edit"><span class="status-dot {{$complaint->status}}"></span><div><b>{{$complaint->ticket_number}}</b><small>{{$complaint->category}} · {{$complaint->location}}</small></div><em>{{$complaint->status}}</em></a>@empty<p>Belum ada pengaduan.</p>@endforelse</div></section>@endif
<aside class="admin-panel health"><small>STATUS SISTEM</small><h2>Portal siap dikelola</h2><div><span><i></i> Website publik</span><b>Online</b></div><div><span><i></i> Database</span><b>Terhubung</b></div><div><span><i></i> Media lokal</span><b>{{number_format($storageSize/1048576,1)}} MB</b></div><div><span><i></i> Asisten PUPR</span><b>Basis resmi</b></div></aside></div>
@if(in_array($role,['SUPER_ADMIN','ADMIN']))<section class="admin-panel audit-preview"><header><div><small>JEJAK PERUBAHAN</small><h2>Aktivitas pengelola terbaru</h2></div><a href="/admin/audits">Lihat audit →</a></header>@forelse($recentAudits as $log)<div><b>{{$log->action}} · {{$log->module}}</b><span>{{$log->user?->name ?? 'Sistem'}} — {{$log->created_at->diffForHumans()}}</span><p>{{$log->description}}</p></div>@empty<p>Belum ada aktivitas tercatat.</p>@endforelse</section>@endif
@endsection
