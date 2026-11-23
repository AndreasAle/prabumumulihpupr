@php
    $staff = 'Pelaksana · Penelaah Teknis Kebijakan · P3K';
    $departments = [
        ['Bidang Program', [
            'Analis Kebijakan Ahli Muda',
            'Analis Kebijakan Ahli Muda',
            'Analis Kebijakan Ahli Muda',
        ]],
        ['Bidang Cipta Karya', [
            'Teknik Tata Bangunan dan Perumahan Ahli Muda',
            'Teknik Tata Bangunan dan Perumahan Ahli Muda',
            'Teknik Penyehatan Lingkungan Ahli Muda',
        ]],
        ['Bidang Bina Marga', [
            'Teknik Jalan dan Jembatan Ahli Muda',
            'Teknik Jalan dan Jembatan Ahli Muda',
            'Teknik Jalan dan Jembatan Ahli Muda',
        ]],
        ['Bidang Tata Ruang', [
            'Penata Ruang Ahli Muda',
            'Penata Ruang Ahli Muda',
            'Penata Ruang Ahli Muda',
        ]],
        ['Bidang Pertanahan', [
            'Analis Kebijakan Ahli Muda',
            'Analis Kebijakan Ahli Muda',
            'Analis Kebijakan Ahli Muda',
        ]],
    ];
@endphp
<section class="org-chart-detailed shell" aria-labelledby="org-chart-title">
    <div class="org-chart-intro"><div><span class="kicker">BAGAN LENGKAP</span><h2 id="org-chart-title">Struktur organisasi dan lini pelaksana.</h2></div><p>Bagan digital ini mengikuti susunan pada dokumen resmi, dari pimpinan hingga jabatan fungsional dan unsur pelaksana.</p></div>
    <div class="org-scroll" tabindex="0" aria-label="Bagan organisasi lengkap, geser horizontal untuk melihat seluruh bagian">
    <div class="org-canvas">
    <div class="org-leadership">
        <article class="org-card org-card-root"><small>PIMPINAN</small><h3>Kepala Dinas</h3><p>Penanggung jawab penyelenggaraan urusan pekerjaan umum dan penataan ruang.</p></article>
        <div class="org-connector vertical"></div>
        <article class="org-card org-card-secretary"><small>SEKRETARIAT</small><h3>Sekretaris</h3><p>Koordinasi administrasi, umum, tata usaha, dan keuangan.</p></article>
        <div class="org-secretariat-tree">
            @foreach([['Kasubag Umum dan Tata Usaha',$staff],['Kasubag Keuangan',$staff]] as $subunit)
                <article><small>SUBBAGIAN</small><h4>{{$subunit[0]}}</h4><div><span>UNIT PENDUKUNG</span><p>{{$subunit[1]}}</p></div></article>
            @endforeach
        </div>
    </div>
    <div class="org-main-connector"><span></span></div>
    <div class="org-departments">
        @foreach($departments as [$department,$roles])
            <section class="org-department">
                <header><span>{{str_pad($loop->iteration,2,'0',STR_PAD_LEFT)}}</span><small>UNIT TEKNIS</small><h3>{{$department}}</h3></header>
                <div class="org-role-list">
                    @foreach($roles as $role)
                        <article><small>JABATAN FUNGSIONAL</small><h4>{{$role}}</h4><div><span>PELAKSANA</span><p>{{$staff}}</p></div></article>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
    </div>
    </div>
    <p class="org-scroll-hint">← Geser bagan ke kiri atau kanan untuk melihat seluruh struktur →</p>
    @if($officialDocument)<div class="org-source"><div><small>DOKUMEN SUMBER</small><h2>{{$officialDocument->title}}</h2><p>{{$officialDocument->description}}</p></div><a class="btn btn-navy" href="{{asset('storage/'.$officialDocument->file_path)}}" target="_blank" rel="noopener">Lihat bagan resmi ↗</a></div>@endif
</section>
