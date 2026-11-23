<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('services')->where('slug', 'persetujuan-kkpr')->update([
            'name' => 'Persetujuan Kesesuaian Kegiatan Pemanfaatan Ruang (PKKPR)',
            'summary' => 'Panduan pengajuan PKKPR untuk memastikan rencana investasi dan pembangunan sesuai dengan rencana tata ruang Kota Prabumulih.',
            'seo_title' => 'Layanan PKKPR Kota Prabumulih',
            'seo_description' => 'Alur, persyaratan, waktu, biaya, dan akses pengajuan PKKPR Kota Prabumulih melalui OSS RBA.',
            'updated_at' => $now,
        ]);

        DB::table('settings')->updateOrInsert(
            ['key' => 'homepage_banner'],
            ['value' => 'storage/materi-resmi/kegiatan-dinas/31.jpg', 'created_at' => $now, 'updated_at' => $now]
        );

        DB::table('documents')->updateOrInsert(
            ['slug' => 'struktur-organisasi-pupr-prabumulih-2025-2029'],
            [
                'title' => 'Struktur Organisasi Dinas PUPR Kota Prabumulih 2025–2029',
                'category' => 'Perencanaan dan Organisasi',
                'year' => 2025,
                'description' => 'Bagan struktur organisasi resmi Dinas PUPR Kota Prabumulih yang bersumber dari materi Renstra 2025–2029. Bagan memuat Kepala Dinas, Sekretaris, unsur tata usaha dan keuangan, serta Bidang Program, Cipta Karya, Bina Marga, Tata Ruang, dan Pertanahan.',
                'file_path' => 'materi-resmi/dokumen/struktur-organisasi-pupr-prabumulih-2025-2029.pdf',
                'external_url' => null,
                'file_type' => 'PDF',
                'file_size' => '436 KB',
                'is_public' => true,
                'publish_status' => 'PUBLISHED',
                'published_at' => $now,
                'seo_title' => 'Struktur Organisasi Dinas PUPR Kota Prabumulih 2025–2029',
                'seo_description' => 'Lihat dan unduh bagan struktur organisasi resmi Dinas PUPR Kota Prabumulih.',
                'seo_image' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('media_assets')->updateOrInsert(
            ['file_path' => 'materi-resmi/video/alur-pengajuan-pkppr.mp4'],
            [
                'title' => 'Peningkatan Pelayanan Pemanfaatan Ruang Wilayah Kota Prabumulih untuk Investasi dan Pembangunan',
                'caption' => '/storage/materi-resmi/video/alur-pengajuan-pkppr-poster.jpg',
                'alt_text' => 'Video alur pengajuan PKKPR melalui OSS, verifikasi, pembayaran PNBP, survei dan pembuatan Pertek, kajian Forum Penataan Ruang, hingga persetujuan.',
                'mime_type' => 'video/mp4',
                'source_url' => 'service:persetujuan-kkpr',
                'file_size' => 15730856,
                'width' => 1280,
                'height' => 720,
                'credit' => 'Dinas PUPR Kota Prabumulih',
                'category' => 'Penataan Ruang',
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('ai_knowledge_sources')->updateOrInsert(
            ['title' => 'Struktur Organisasi dan Alur Pelayanan PKKPR'],
            [
                'source_type' => 'OFFICIAL_MATERIAL',
                'content' => 'Struktur organisasi Dinas PUPR Kota Prabumulih dalam materi Renstra 2025–2029 dipimpin Kepala Dinas dan didukung Sekretaris, Kasubag Umum dan Tata Usaha, Kasubag Keuangan, Bidang Program, Bidang Cipta Karya, Bidang Bina Marga, Bidang Tata Ruang, dan Bidang Pertanahan. Video pelayanan pemanfaatan ruang menjelaskan alur pengajuan PKKPR: pengajuan melalui OSS, verifikasi, pembayaran PNBP, survei dan pembuatan Pertimbangan Teknis (Pertek), kajian Forum Penataan Ruang, kemudian persetujuan PKKPR.',
                'is_enabled' => true,
                'indexed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('services')->where('slug', 'persetujuan-kkpr')->update([
            'name' => 'Persetujuan Kesesuaian Kegiatan Pemanfaatan Ruang (KKPR)',
            'summary' => 'Layanan kesesuaian rencana kegiatan pemanfaatan ruang dengan rencana tata ruang yang berlaku.',
        ]);
        DB::table('settings')->where('key', 'homepage_banner')->update([
            'value' => 'images/hero/emblem-geometris-emas-biru.png',
        ]);
        DB::table('documents')->where('slug', 'struktur-organisasi-pupr-prabumulih-2025-2029')->delete();
        DB::table('media_assets')->where('file_path', 'materi-resmi/video/alur-pengajuan-pkppr.mp4')->delete();
        DB::table('ai_knowledge_sources')->where('title', 'Struktur Organisasi dan Alur Pelayanan PKKPR')->delete();
    }
};
