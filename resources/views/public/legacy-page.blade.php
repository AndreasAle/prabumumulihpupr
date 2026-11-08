@extends('layouts.public')
@push('head')<link rel="stylesheet" href="/css/legacy-editorial.css?v=20261005">@endpush
@section('title',$page->seo_title ?: $page->title)
@section('meta_description',$page->seo_description ?: ($page->excerpt ?: Str::limit(strip_tags($page->content),155)))
@php
$pageLinks=['visimisi'=>'Visi & Misi','tugasfungsi'=>'Tugas & Fungsi','contact-2'=>'Struktur & Kontak','program-penyelenggaraan-penataan-ruang'=>'Program Penataan Ruang','strategi-antisipatif-organisasi'=>'Strategi Organisasi','capaiankinerja'=>'Capaian Kinerja','saran-dan-pengaduan'=>'Saran & Pengaduan'];
@endphp
@section('content')
<section class="legacy-editorial-head legacy-head-{{$page->slug}}"><div class="shell"><div class="breadcrumbs"><a href="/">Beranda</a><span>/</span><a href="/profil">Profil Dinas</a><span>/</span>{{$pageLinks[$page->slug]??'Informasi'}}</div><div class="legacy-head-grid"><div><span class="kicker">INFORMASI RESMI DINAS</span><h1>{{$page->title}}</h1>@if($page->excerpt)<p>{{$page->excerpt}}</p>@endif</div><div class="legacy-head-index"><small>DOKUMEN</small><strong>{{str_pad(array_search($page->slug,array_keys($pageLinks))+1,2,'0',STR_PAD_LEFT)}}</strong><span>Dinas PUPR<br>Kota Prabumulih</span></div></div></div></section>
<div class="legacy-editorial-layout shell"><aside class="legacy-page-nav"><span>JELAJAHI INFORMASI</span>@foreach($pageLinks as $slug=>$label)<a class="{{$page->slug===$slug?'active':''}}" href="/halaman/{{$slug}}"><small>{{str_pad($loop->iteration,2,'0',STR_PAD_LEFT)}}</small>{{$label}}<b>→</b></a>@endforeach</aside><article class="legacy-article">@if($page->featured_image)<img class="legacy-featured" src="{{$page->featured_image}}" alt="{{$page->title}}">@endif<div class="legacy-meta"><span>Dipublikasikan {{$page->published_at?->translatedFormat('d F Y') ?: 'melalui portal resmi'}}</span><span>Informasi kelembagaan Dinas PUPR</span></div><div class="legacy-content legacy-content-{{$page->slug}}">{!! $page->content !!}</div><div class="legacy-source"><span>SUMBER INFORMASI</span><p>Materi dipindahkan dari website resmi sebelumnya dan dikelola kembali melalui CMS Dinas PUPR Kota Prabumulih.</p>@if($page->source_url)<a href="{{$page->source_url}}" target="_blank" rel="noopener">Lihat sumber asli ↗</a>@endif</div>@php
$keys=array_keys($pageLinks);
$pos=array_search($page->slug,$keys);
$next=$keys[($pos+1)%count($keys)];
@endphp<nav class="legacy-next"><small>INFORMASI SELANJUTNYA</small><a href="/halaman/{{$next}}"><span>{{$pageLinks[$next]}}</span><b>→</b></a></nav></article></div>
@endsection
