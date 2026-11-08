@extends('layouts.public')

@section('title', 'Pencarian')

@section('content')
<section class="page-hero">
    <div class="shell">
        <span class="kicker">PENCARIAN PORTAL</span>
        <h1>Temukan informasi.</h1>
        <form class="hero-search" action="/cari" method="get">
            <input name="q" value="{{$q}}" placeholder="Cari layanan, program, berita, dokumen…" autofocus>
            <button type="submit">Telusuri</button>
        </form>
    </div>
</section>

<section class="section shell search-results">
    @if($q === '')
        <div class="empty-state">
            <b>Masukkan kata kunci.</b>
            <p>Telusuri seluruh informasi yang telah dipublikasikan.</p>
        </div>
    @else
        <h2>Hasil untuk “{{$q}}”</h2>

        @foreach($results as $group => $items)
            @if($items->isNotEmpty())
                @php($resultType = ['Berita' => 'berita', 'Layanan' => 'layanan', 'Proyek' => 'proyek', 'Dokumen' => 'transparansi'][$group])
                <div class="result-group">
                    <h3>{{$group}} <span>{{$items->count()}}</span></h3>
                    @foreach($items as $item)
                        <a href="/{{$resultType}}/{{$item->slug}}">
                            <b>{{$item->title ?? $item->name}}</b>
                            <span>→</span>
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach

        @if(collect($results)->flatten()->isEmpty())
            <div class="empty-state">
                <b>Informasi tidak ditemukan.</b>
                <p>Coba gunakan kata kunci yang lebih umum.</p>
            </div>
        @endif
    @endif
</section>
@endsection
