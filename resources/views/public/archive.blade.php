@extends('layouts.public')
@section('title','Arsip Website Lama')
@section('content')
<section class="archive-head"><div class="shell"><div class="breadcrumbs"><a href="/">Beranda</a><span>/</span>Arsip</div><span class="kicker">MIGRASI KONTEN</span><h1>Informasi lama,<br><em>tetap terjaga.</em></h1><div class="archive-summary"><div><strong>{{$pages->count()}}</strong><span>Halaman teks</span></div><div><strong>{{$mediaCount}}</strong><span>Aset media</span></div><p>Konten berikut dimigrasikan dari website sebelumnya untuk menjaga kesinambungan informasi publik.</p></div></div></section>
<section class="archive-list shell"><div class="archive-list-head"><span>DAFTAR HALAMAN</span><span>{{$pages->count()}} arsip tersedia</span></div>@foreach($pages as $i=>$page)<a href="/halaman/{{$page->slug}}"><span>{{str_pad($i+1,2,'0',STR_PAD_LEFT)}}</span><div><small>{{$page->published_at?->translatedFormat('Y')}}</small><h2>{{$page->title}}</h2>@if($page->excerpt)<p>{{$page->excerpt}}</p>@endif</div><b>↗</b></a>@endforeach</section>
@endsection
