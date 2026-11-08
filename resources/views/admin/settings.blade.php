@extends('layouts.admin') @section('title','Pengaturan Portal') @section('header','Pengaturan Portal')
@section('content')
<div class="admin-page-head"><div><span class="kicker">KONFIGURASI WEBSITE</span><h1>Identitas, banner, menu & kontak</h1><p>Nilai yang disimpan langsung digunakan oleh website publik.</p></div><form method="post" action="/admin/backup">@csrf<button class="btn btn-navy">Unduh Backup Lengkap</button></form></div>
<form method="post" enctype="multipart/form-data" class="admin-form" action="/admin/settings">@csrf
@php($groups=[
'Identitas Website'=>['site_name'=>'Nama instansi','site_subtitle'=>'Nama wilayah','seo_description'=>'Deskripsi SEO utama'],
'Kontak & Layanan'=>['address'=>'Alamat','phone'=>'Telepon','email'=>'Email','whatsapp'=>'WhatsApp','service_hours'=>'Jam layanan'],
'Media Sosial'=>['facebook'=>'Facebook','instagram'=>'Instagram','youtube'=>'YouTube','tiktok'=>'TikTok'],
'Homepage'=>['homepage_kicker'=>'Label banner','homepage_title'=>'Judul banner (boleh memakai <em>)','homepage_description'=>'Deskripsi banner','homepage_about_image'=>'URL gambar profil singkat'],
'Navigasi & Footer'=>['navigation_json'=>'Menu utama dalam JSON: [["Label","/tautan"]]','footer_description'=>'Deskripsi footer','footer_note'=>'Catatan footer'],
])
<section class="admin-panel form-section setting-section"><h2>Logo & Banner Homepage</h2><label>Unggah logo baru<input type="file" name="logo_file" accept="image/*"><small>Saat ini: {{$settings['site_logo']??'-'}} · maksimal 4 MB</small></label><label>Unggah banner baru<input type="file" name="banner_file" accept="image/*"><small>Saat ini: {{$settings['homepage_banner']??'-'}} · maksimal 12 MB</small></label></section>
@foreach($groups as $title=>$fields)<section class="admin-panel form-section setting-section"><h2>{{$title}}</h2>@foreach($fields as $key=>$label)<label>{{$label}}@if(str_contains($key,'description')||str_contains($key,'title')||$key==='navigation_json')<textarea name="settings[{{$key}}]" rows="4">{{old("settings.$key",$settings[$key]??'')}}</textarea>@else<input name="settings[{{$key}}]" value="{{old("settings.$key",$settings[$key]??'')}}">@endif</label>@endforeach</section>@endforeach
<div class="form-actions"><span>Perubahan berlaku setelah disimpan.</span><button class="btn btn-navy">Simpan semua pengaturan</button></div></form>
@endsection
