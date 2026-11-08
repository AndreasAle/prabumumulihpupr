<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $defaults = [
            'site_name'=>'DINAS PUPR','site_subtitle'=>'KOTA PRABUMULIH','site_logo'=>'images/brand/logo-pupr.png',
            'homepage_banner'=>'images/hero/emblem-geometris-emas-biru.png','homepage_kicker'=>'DINAS PUPR KOTA PRABUMULIH',
            'homepage_title'=>'Membangun kota dengan karya yang <em>bermakna.</em>',
            'homepage_description'=>'Informasi pembangunan, layanan masyarakat, dan keterbukaan publik dalam satu portal resmi.',
            'address'=>'Kota Prabumulih, Sumatera Selatan','service_hours'=>'Senin–Jumat, mengikuti jam kerja Pemerintah Kota Prabumulih',
            'footer_description'=>'Portal informasi dan layanan digital Dinas Pekerjaan Umum dan Penataan Ruang Kota Prabumulih.',
            'footer_note'=>'Portal resmi Pemerintah Kota Prabumulih',
            'navigation_json'=>json_encode([['Profil Dinas','/profil'],['Program & Kinerja','/proyek'],['Layanan Publik','/layanan'],['Transparansi','/transparansi'],['Informasi','/berita'],['Media','/galeri']], JSON_UNESCAPED_SLASHES),
        ];
        foreach ($defaults as $key=>$value) DB::table('settings')->updateOrInsert(['key'=>$key], ['value'=>$value,'group'=>'website','created_at'=>now(),'updated_at'=>now()]);

        DB::table('projects')->where('slug','like','%-demo')->delete();
        DB::table('documents')->where('description','like','%demonstrasi%')->delete();
        DB::table('agendas')->where('description','Agenda demonstrasi')->delete();
        DB::table('news')->where('content','like','%konten demonstrasi%')->delete();
        DB::table('services')->where('description','like','%data demonstrasi%')->delete();

        $services = [
            ['Pengaduan Infrastruktur','pengaduan-infrastruktur','Pengaduan','Laporkan kondisi jalan, drainase, jembatan, atau fasilitas publik melalui nomor tiket.'],
            ['Informasi Penataan Ruang','informasi-penataan-ruang','Penataan Ruang','Akses informasi penyelenggaraan penataan ruang Kota Prabumulih.'],
            ['Permohonan Informasi Publik','permohonan-informasi-publik','Informasi Publik','Ajukan kebutuhan informasi dan dokumen publik kepada Dinas PUPR.'],
        ];
        foreach($services as $i=>$s) DB::table('services')->updateOrInsert(['slug'=>$s[1]],['name'=>$s[0],'category'=>$s[2],'summary'=>$s[3],'description'=>$s[3],'processing_time'=>'Sesuai verifikasi petugas','cost_information'=>'Sesuai ketentuan resmi','officer_contact'=>'Kanal resmi Dinas PUPR','is_published'=>true,'publish_status'=>'PUBLISHED','sort_order'=>$i,'created_at'=>now(),'updated_at'=>now()]);

        DB::table('statistics')->delete();
        foreach ([
            ['Publikasi Resmi',DB::table('news')->where('status','PUBLISHED')->count(),'Artikel yang tersedia di portal'],
            ['Informasi Publik',DB::table('pages')->where('status','PUBLISHED')->count(),'Halaman informasi dari arsip resmi'],
            ['Arsip Media',DB::table('media_assets')->count(),'Foto dan video pada pustaka media'],
            ['Kanal Layanan',count($services),'Layanan yang dapat diakses warga'],
        ] as $i=>$s) DB::table('statistics')->insert(['label'=>$s[0],'value'=>(string)$s[1],'suffix'=>'','description'=>$s[2],'sort_order'=>$i,'created_at'=>now(),'updated_at'=>now()]);
    }
    public function down(): void {}
};
