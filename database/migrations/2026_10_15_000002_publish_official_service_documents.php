<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $file = 'materi-resmi/dokumen/materi-website-pupr-prabumulih.pdf';

        DB::table('pages')->updateOrInsert(['slug' => 'maklumat-pelayanan'], [
            'title' => 'Maklumat Pelayanan Dinas PUPR Kota Prabumulih',
            'excerpt' => 'Komitmen penyelenggaraan pelayanan sesuai standar yang telah ditetapkan.',
            'content' => '<h2>Maklumat Pelayanan</h2><blockquote>Dengan ini kami menyatakan sanggup menyelenggarakan pelayanan sesuai dengan standar pelayanan yang telah ditetapkan.</blockquote><p>Kami akan memberikan pelayanan yang cepat, mudah, transparan, akuntabel, profesional, ramah, adil, tidak diskriminatif, dan berorientasi pada kepuasan masyarakat.</p><p>Apabila dalam penyelenggaraan pelayanan kami tidak sesuai dengan standar pelayanan yang telah ditetapkan, kami siap menerima sanksi, melakukan perbaikan, dan memberikan tindak lanjut sesuai dengan ketentuan peraturan perundang-undangan.</p>',
            'featured_image' => '/storage/materi-resmi/infografis/materi-02.webp',
            'source_url' => '/storage/'.$file,
            'status' => 'PUBLISHED', 'published_at' => $now,
            'seo_title' => 'Maklumat Pelayanan Dinas PUPR Kota Prabumulih',
            'seo_description' => 'Komitmen pelayanan Dinas PUPR Kota Prabumulih.',
            'seo_image' => '/storage/materi-resmi/infografis/materi-02.webp',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('pages')->updateOrInsert(['slug' => 'hasil-ikm-2026'], [
            'title' => 'Hasil Indeks Kepuasan Masyarakat Semester II Tahun 2026',
            'excerpt' => 'Hasil IKM Dinas PUPR Kota Prabumulih sebesar 95,97 persen.',
            'content' => '<h2>Indeks Kepuasan Masyarakat</h2><blockquote>95,97%</blockquote><p>Nilai tersebut merupakan Hasil Indeks Kepuasan Masyarakat Semester II Tahun 2026 Dinas PUPR Kota Prabumulih sebagaimana tercantum pada materi resmi yang diserahkan untuk pembaruan portal.</p><p>Informasi ini menjadi salah satu bahan evaluasi untuk menjaga mutu layanan dan memperkuat tindak lanjut perbaikan pelayanan publik.</p>',
            'featured_image' => '/storage/materi-resmi/infografis/materi-03.webp',
            'source_url' => '/storage/'.$file,
            'status' => 'PUBLISHED', 'published_at' => $now,
            'seo_title' => 'Hasil IKM 2026 Dinas PUPR Kota Prabumulih',
            'seo_description' => 'Hasil Indeks Kepuasan Masyarakat Semester II Tahun 2026 Dinas PUPR Kota Prabumulih.',
            'seo_image' => '/storage/materi-resmi/infografis/materi-03.webp',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('documents')->updateOrInsert(['slug' => 'materi-pelayanan-kkpr-pbg-2026'], [
            'title' => 'Materi Pelayanan KKPR dan PBG Dinas PUPR Kota Prabumulih',
            'category' => 'Pelayanan Publik', 'year' => 2026,
            'description' => 'Materi resmi yang memuat profil pelayanan, maklumat, IKM, KKPR, PBG, persyaratan, alur, waktu, biaya, kontak, dan dasar hukum.',
            'file_path' => $file, 'file_type' => 'PDF', 'file_size' => '20,1 MB',
            'is_public' => true, 'publish_status' => 'PUBLISHED', 'published_at' => $now,
            'seo_title' => 'Materi Pelayanan KKPR dan PBG Kota Prabumulih',
            'seo_description' => 'Unduh materi pelayanan KKPR dan PBG Dinas PUPR Kota Prabumulih.',
            'seo_image' => '/storage/materi-resmi/infografis/materi-05.webp',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('statistics')->where('label', 'Informasi Publik')->delete();
        $statistics = [
            ['Indeks Kepuasan Masyarakat', '95.97', '%', 'Hasil IKM Semester II Tahun 2026', 0],
            ['Publikasi Resmi', (string) DB::table('news')->where('status', 'PUBLISHED')->count(), '', 'Artikel kegiatan yang tersedia di portal', 1],
            ['Arsip Media', (string) DB::table('media_assets')->count(), '', 'Foto, video, dan infografis pada pustaka media', 2],
            ['Kanal Layanan', (string) DB::table('services')->where('is_published', true)->count(), '', 'Layanan yang dapat diakses masyarakat', 3],
        ];
        foreach ($statistics as [$label, $value, $suffix, $description, $sort]) {
            DB::table('statistics')->updateOrInsert(['label' => $label], [
                'value' => $value, 'suffix' => $suffix, 'description' => $description,
                'sort_order' => $sort, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        DB::table('statistics')->whereNotIn('label', array_column($statistics, 0))->delete();
    }

    public function down(): void
    {
        DB::table('pages')->whereIn('slug', ['maklumat-pelayanan', 'hasil-ikm-2026'])->delete();
        DB::table('documents')->where('slug', 'materi-pelayanan-kkpr-pbg-2026')->delete();
    }
};
