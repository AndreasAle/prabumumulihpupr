@extends('layouts.admin')
@php($labels=['news'=>'Berita','pages'=>'Halaman','services'=>'Layanan Publik','projects'=>'Proyek Infrastruktur','documents'=>'Dokumen Publik','agendas'=>'Agenda','statistics'=>'Statistik','media'=>'Media Library','complaints'=>'Pengaduan','users'=>'Pengguna & Role','knowledge'=>'Basis Pengetahuan AI','audits'=>'Audit Aktivitas'])
@section('title',$labels[$module]) @section('header',$labels[$module])
@section('content')
<div class="admin-page-head"><div><span class="kicker">KELOLA PORTAL</span><h1>{{$labels[$module]}}</h1><p>Kelola, tinjau, dan publikasikan data portal secara terkontrol.</p></div><div class="quick-actions">@if($module==='complaints')<a href="/admin/complaints-export">Ekspor CSV</a>@elseif($module!=='audits')<a class="btn btn-navy" href="/admin/{{$module}}/create">+ Tambah data</a>@endif</div></div>
<section class="admin-panel">
<form class="table-tools @if($module==='complaints') complaint-filters @endif">
    <input name="q" value="{{request('q')}}" placeholder="{{$module==='complaints' ? 'Cari nomor tiket…' : 'Cari data…'}}">
    @if($module==='complaints')
        <select name="status"><option value="">Semua status</option>@foreach(['NEW'=>'Baru','VERIFIED'=>'Terverifikasi','IN_PROCESS'=>'Diproses','RESOLVED'=>'Selesai','REJECTED'=>'Ditolak'] as $value=>$label)<option value="{{$value}}" @selected(request('status')===$value)>{{$label}}</option>@endforeach</select>
        <select name="category"><option value="">Semua kategori</option>@foreach($complaintCategories as $category)<option @selected(request('category')===$category)>{{$category}}</option>@endforeach</select>
        <label>Dari<input type="date" name="date_from" value="{{request('date_from')}}"></label><label>Sampai<input type="date" name="date_to" value="{{request('date_to')}}"></label>
        <select name="sort"><option value="created_at" @selected(request('sort','created_at')==='created_at')>Urut tanggal masuk</option><option value="updated_at" @selected(request('sort')==='updated_at')>Urut diperbarui</option><option value="status" @selected(request('sort')==='status')>Urut status</option><option value="category" @selected(request('sort')==='category')>Urut kategori</option></select>
        <select name="direction"><option value="desc" @selected(request('direction','desc')==='desc')>Terbaru / Z–A</option><option value="asc" @selected(request('direction')==='asc')>Terlama / A–Z</option></select>
    @endif
    <button>Terapkan</button>@if($module==='complaints' && request()->hasAny(['q','status','category','date_from','date_to','sort','direction']))<a href="/admin/complaints">Reset</a>@endif
</form>
<div class="data-table"><table><thead><tr><th>Data</th><th>Status/Kategori</th><th>Diperbarui</th><th>Aksi</th></tr></thead><tbody>
@forelse($items as $item)<tr><td><b>{{$module==='complaints' ? $item->ticket_number : ($item->title ?? $item->name ?? $item->label ?? $item->description)}}</b><small>{{$module==='complaints' ? $item->name.' · '.$item->location : ($item->excerpt ?? $item->summary ?? $item->location ?? $item->email ?? $item->mime_type ?? '')}}</small>@if($module==='complaints')<small>{{$item->category}} · {{$item->created_at?->format('d M Y H:i')}}</small>@endif</td><td><span class="table-status">{{$item->publish_status ?? $item->status ?? $item->category ?? $item->role ?? ($item->is_enabled?'AKTIF':'NONAKTIF')}}</span></td><td>{{$item->updated_at?->format('d M Y H:i')}}</td><td><div class="table-actions">@if(in_array($module,['news','pages','services','projects','documents']))<a target="_blank" href="/admin/preview/{{$module}}/{{$item->id}}">Preview</a>@endif @if($module==='complaints')<a target="_blank" href="{{route('admin.complaints.print',$item)}}">Cetak A4</a>@endif @if($module!=='audits')<a href="/admin/{{$module}}/{{$item->id}}/edit">Edit</a>@endif @if(!in_array($module,['complaints','audits'],true))<form method="post" action="/admin/{{$module}}/{{$item->id}}" onsubmit="return confirm('Hapus data ini?')">@csrf @method('delete')<button>Hapus</button></form>@endif</div></td></tr>
@empty<tr><td colspan="4" class="empty-cell">Belum ada data pada modul ini.</td></tr>@endforelse
</tbody></table></div>{{$items->links()}}</section>
@endsection
