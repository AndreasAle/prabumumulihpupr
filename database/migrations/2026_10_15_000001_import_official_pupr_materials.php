<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $services = [
            [
                'name' => 'Persetujuan Kesesuaian Kegiatan Pemanfaatan Ruang (KKPR)',
                'slug' => 'persetujuan-kkpr',
                'category' => 'Penataan Ruang',
                'summary' => 'Layanan kesesuaian rencana kegiatan pemanfaatan ruang dengan rencana tata ruang yang berlaku.',
                'description' => 'KKPR memastikan setiap rencana pemanfaatan ruang sesuai dengan rencana tata ruang yang berlaku. Permohonan dilakukan melalui OSS RBA dan diverifikasi sesuai kewenangan pemerintah daerah.',
                'requirements' => json_encode([
                    'Koordinat lokasi',
                    'Koordinat lokasi dalam format SHP',
                    'Kebutuhan luas',
                    'Informasi penguasaan tanah berupa surat kepemilikan',
                    'Informasi jenis kegiatan/jenis usaha, lingkup usaha, dan nilai investasi',
                    'Rencana jumlah lantai bangunan',
                    'Rencana luas lantai bangunan',
                    'Rencana teknis bangunan dan/atau rencana induk kawasan',
                    'PNBP pelayanan penerbitan KKPR',
                ]),
                'steps' => json_encode([
                    'Pelaku usaha mendaftar akun dan mengisi data kegiatan usaha melalui OSS RBA.',
                    'Operator memverifikasi dokumen permohonan atau usulan kegiatan.',
                    'Sistem OSS memilah permohonan sesuai kewenangan pemerintah.',
                    'Operator OSS memvalidasi persyaratan yang menjadi kewenangan Pemerintah Kota Prabumulih.',
                    'Dokumen yang belum lengkap dikembalikan melalui OSS untuk diperbaiki.',
                    'Jika lengkap, pemohon menerima slip pembayaran PNBP dan BPN menerbitkan Pertimbangan Teknis Pertanahan.',
                    'Tim teknis melakukan penilaian dokumen dan, bila diperlukan, peninjauan lapangan untuk menghasilkan pertimbangan FPR.',
                    'Pertimbangan FPR divalidasi, ditandatangani, dan diunggah ke OSS untuk diteruskan kepada DPMPTSP.',
                ]),
                'processing_time' => 'Maksimal 20 hari',
                'cost_information' => 'Sesuai Peraturan Menteri Keuangan Nomor 143/PMK.02/2021',
                'online_url' => 'https://oss.go.id/',
                'officer_contact' => 'dispupr.kotaprabumulih@gmail.com · 0821 2223 7969 · @dispupr_prabumulih',
                'is_published' => true,
                'publish_status' => 'PUBLISHED',
                'sort_order' => 0,
                'seo_title' => 'Layanan KKPR Kota Prabumulih',
                'seo_description' => 'Persyaratan, alur, waktu, biaya, dan kontak layanan KKPR Dinas PUPR Kota Prabumulih.',
            ],
            [
                'name' => 'Persetujuan Bangunan Gedung (PBG)',
                'slug' => 'persetujuan-bangunan-gedung-pbg',
                'category' => 'Bangunan Gedung',
                'summary' => 'Informasi persyaratan dan proses penerbitan PBG melalui Sistem Informasi Manajemen Bangunan Gedung.',
                'description' => 'PBG adalah persetujuan yang diberikan kepada pemilik bangunan untuk membangun baru, mengubah, memperluas, mengurangi, dan/atau merawat bangunan gedung sesuai standar teknis bangunan gedung.',
                'requirements' => json_encode([
                    'Rumah tinggal sederhana: persetujuan tetangga, PKKPR/KRK, KTP/KITAS, gambar teknis, sertifikat ahli, site plan, spesifikasi, dan dokumen lahan.',
                    'Rumah tinggal tidak sederhana: seluruh syarat rumah sederhana ditambah pengelolaan tapak, efisiensi energi dan air, kualitas udara, material ramah lingkungan, dan pengelolaan limbah.',
                    'Bangunan gedung umum: dokumen teknis dan administratif, SIPPT, KKOP, dokumen lingkungan, SKUB, rekomendasi Kominfo, serta peil banjir sesuai kebutuhan bangunan.',
                ]),
                'steps' => json_encode([
                    'Pemohon menyiapkan prasyarat, termasuk KRK, PKKPR, dan dokumen pendukung lainnya.',
                    'Pemohon mendaftar melalui SIMBG dan mengunggah dokumen.',
                    'Operator memeriksa kelengkapan dokumen.',
                    'Pengawas menetapkan TPA/TPT dan jadwal konsultasi.',
                    'Konsultasi teknis dilakukan bersama TPA/TPT.',
                    'Sekretariat PBG menghitung retribusi.',
                    'Kepala Dinas memvalidasi dan menerbitkan Surat Pernyataan Pemenuhan Standar Teknis.',
                    'Pemohon membayar retribusi melalui Bank Pembangunan Daerah.',
                    'Dinas Perizinan menerbitkan PBG.',
                ]),
                'processing_time' => 'Paling lama 28 hari kerja, maksimal 5 kali pemeriksaan',
                'cost_information' => 'Berdasarkan Perda Kota Prabumulih Nomor 3 Tahun 2022',
                'online_url' => 'https://simbg.pu.go.id/',
                'officer_contact' => 'dispupr.kotaprabumulih@gmail.com · 0821 2223 7969 · @dispupr_prabumulih',
                'is_published' => true,
                'publish_status' => 'PUBLISHED',
                'sort_order' => 1,
                'seo_title' => 'Layanan PBG Kota Prabumulih',
                'seo_description' => 'Persyaratan, alur, waktu, biaya, dan kontak layanan PBG Dinas PUPR Kota Prabumulih.',
            ],
        ];

        foreach ($services as $service) {
            DB::table('services')->updateOrInsert(
                ['slug' => $service['slug']],
                [...$service, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $articles = [
            [
                'title' => 'Rangkaian Kegiatan Dinas PUPR: Koordinasi, Survei, dan Pelayanan Lapangan',
                'slug' => 'rangkaian-kegiatan-dinas-pupr-koordinasi-survei-pelayanan-lapangan',
                'category' => 'Kegiatan Dinas',
                'excerpt' => 'Dokumentasi berbagai kegiatan koordinasi, pengukuran, verifikasi, pemantauan, dan pelayanan lapangan Dinas PUPR Kota Prabumulih.',
                'content' => "Dinas PUPR Kota Prabumulih menjalankan kegiatan teknis melalui koordinasi lintas bidang dan peninjauan langsung ke lapangan. Dokumentasi ini memperlihatkan rangkaian kerja mulai dari rapat teknis, pengukuran kondisi jalan, pemeriksaan lingkungan, verifikasi pekerjaan, hingga komunikasi dengan masyarakat.\n\nSetiap kegiatan lapangan menjadi bagian dari proses memperoleh data aktual, mengenali kebutuhan penanganan, serta menyusun tindak lanjut yang lebih tepat. Informasi teknis yang dikumpulkan kemudian dibahas bersama unit terkait agar pekerjaan berjalan sesuai kewenangan dan standar pelayanan.\n\nGaleri pada halaman ini merupakan dokumentasi yang diserahkan sebagai materi resmi website Dinas PUPR Kota Prabumulih.",
                'thumbnail' => '/storage/materi-resmi/kegiatan-dinas/23.jpg',
            ],
            [
                'title' => 'Konsultasi Publik II Rencana Tata Ruang Wilayah Kota Prabumulih',
                'slug' => 'konsultasi-publik-ii-rtrw-kota-prabumulih',
                'category' => 'Penataan Ruang',
                'excerpt' => 'Forum konsultasi publik sebagai ruang penyelarasan masukan dalam penyusunan Rencana Tata Ruang Wilayah Kota Prabumulih.',
                'content' => "Konsultasi Publik II Rencana Tata Ruang Wilayah (RTRW) Kota Prabumulih menjadi ruang pertemuan antara pemerintah dan para pemangku kepentingan untuk membahas arah penataan ruang kota.\n\nMelalui forum ini, materi perencanaan dapat dikaji bersama, masukan dihimpun, dan kepentingan pembangunan diselaraskan agar pemanfaatan ruang berlangsung tertib, berkelanjutan, dan memberi manfaat bagi masyarakat.\n\nDokumentasi memperlihatkan sesi pemaparan, diskusi, serta keterlibatan peserta dalam proses konsultasi publik.",
                'thumbnail' => '/storage/materi-resmi/konsultasi-publik-rtrw/17.jpg',
            ],
            [
                'title' => 'Pelatihan Supervisor K3 Konstruksi Utama Jenjang 6',
                'slug' => 'pelatihan-supervisor-k3-konstruksi-utama-jenjang-6',
                'category' => 'Pengembangan Kompetensi',
                'excerpt' => 'Penguatan pemahaman keselamatan dan kesehatan kerja untuk mendukung pelaksanaan pekerjaan konstruksi yang lebih aman.',
                'content' => "Pelatihan Supervisor K3 Konstruksi Utama Jenjang 6 merupakan bagian dari penguatan kompetensi sumber daya manusia pada penyelenggaraan pekerjaan konstruksi.\n\nKegiatan pembelajaran membahas peran pengawasan, pengendalian risiko, disiplin penerapan prosedur, serta tanggung jawab keselamatan di lingkungan proyek. Dokumentasi kelas dan kegiatan lapangan menunjukkan bahwa pemahaman K3 dibangun melalui pembelajaran sekaligus pengamatan kondisi nyata.\n\nPeningkatan kompetensi K3 mendukung kualitas pekerjaan, melindungi tenaga kerja, dan membantu memastikan kegiatan konstruksi dilaksanakan secara tertib.",
                'thumbnail' => '/storage/materi-resmi/pelatihan-k3/24.jpg',
            ],
            [
                'title' => 'Survei Lapangan dan Monitoring Evaluasi Bidang Cipta Karya',
                'slug' => 'survei-lapangan-monitoring-evaluasi-bidang-cipta-karya',
                'category' => 'Cipta Karya',
                'excerpt' => 'Peninjauan kondisi lingkungan dan bangunan sebagai bahan evaluasi teknis Bidang Cipta Karya.',
                'content' => "Bidang Cipta Karya melaksanakan survei lapangan serta monitoring dan evaluasi untuk memperoleh gambaran langsung mengenai kondisi lingkungan dan fasilitas yang ditinjau.\n\nKegiatan ini meliputi pengamatan kondisi eksisting, pencatatan temuan, dokumentasi lokasi, dan koordinasi dengan pihak terkait. Data lapangan menjadi bahan penting untuk mengidentifikasi kebutuhan penanganan serta menyusun tindak lanjut yang sesuai.\n\nPendekatan berbasis kondisi aktual membantu proses evaluasi dilakukan secara lebih objektif dan dapat dipertanggungjawabkan.",
                'thumbnail' => '/storage/materi-resmi/survei-monev-cipta-karya/20.jpg',
            ],
        ];

        foreach ($articles as $article) {
            DB::table('news')->updateOrInsert(
                ['slug' => $article['slug']],
                [...$article, 'author' => 'Dinas PUPR Kota Prabumulih', 'status' => 'PUBLISHED',
                    'published_at' => $now, 'seo_title' => $article['title'],
                    'seo_description' => $article['excerpt'], 'seo_image' => $article['thumbnail'],
                    'created_at' => $now, 'updated_at' => $now]
            );
        }

        $photos = [
            'kegiatan-dinas' => [
                'label' => 'Kegiatan Dinas PUPR', 'article' => $articles[0]['slug'],
                'items' => [
                    '16.jpg' => 'Koordinasi dan pertemuan kelembagaan Dinas PUPR',
                    '22.jpg' => 'Survei kondisi ruas jalan oleh tim teknis',
                    '23.jpg' => 'Pengukuran lapangan pada ruas jalan',
                    '26.jpg' => 'Peninjauan kondisi lingkungan perkotaan',
                    '27.jpg' => 'Peninjauan area pekerjaan bersama tim',
                    '28.jpg' => 'Briefing keselamatan pada kegiatan lapangan',
                    '29.jpg' => 'Rapat koordinasi teknis antarbidang',
                    '30.jpg' => 'Monitoring pekerjaan lingkungan permukiman',
                    '31.jpg' => 'Verifikasi kondisi lapangan',
                    '32.jpg' => 'Koordinasi pelayanan bersama masyarakat',
                    '33.jpg' => 'Kegiatan koordinasi pelayanan daerah',
                ],
            ],
            'konsultasi-publik-rtrw' => [
                'label' => 'Konsultasi Publik RTRW', 'article' => $articles[1]['slug'],
                'items' => [
                    '17.jpg' => 'Konsultasi Publik II RTRW Kota Prabumulih',
                    '18.jpg' => 'Peserta Konsultasi Publik II RTRW',
                    '19.jpg' => 'Sesi pemaparan dan diskusi RTRW',
                ],
            ],
            'pelatihan-k3' => [
                'label' => 'Pelatihan K3', 'article' => $articles[2]['slug'],
                'items' => [
                    '24.jpg' => 'Pelatihan Supervisor K3 Konstruksi Utama Jenjang 6',
                    '25.jpg' => 'Kegiatan lapangan peserta pelatihan K3',
                ],
            ],
            'survei-monev-cipta-karya' => [
                'label' => 'Survei dan Monev Cipta Karya', 'article' => $articles[3]['slug'],
                'items' => [
                    '20.jpg' => 'Survei kondisi saluran lingkungan',
                    '21.jpg' => 'Monitoring dan evaluasi lokasi Bidang Cipta Karya',
                ],
            ],
        ];

        foreach ($photos as $folder => $group) {
            foreach ($group['items'] as $filename => $title) {
                DB::table('media_assets')->updateOrInsert(
                    ['file_path' => "materi-resmi/{$folder}/{$filename}"],
                    ['title' => $title, 'caption' => $title, 'alt_text' => $title,
                        'mime_type' => 'image/jpeg', 'file_size' => null, 'width' => 1920, 'height' => 1080,
                        'published_at' => $now, 'credit' => 'Dinas PUPR Kota Prabumulih',
                        'category' => $group['label'], 'source_url' => 'article:'.$group['article'],
                        'created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        $infographics = [
            1 => ['Selamat Datang di Dinas PUPR Kota Prabumulih', 'Profil dan Pimpinan', 'institutional'],
            2 => ['Maklumat Pelayanan Dinas PUPR', 'Maklumat Pelayanan', 'institutional'],
            3 => ['Hasil Indeks Kepuasan Masyarakat 2026', 'Indeks Kepuasan Masyarakat', 'institutional'],
            4 => ['Visi, Misi, dan Motto Dinas PUPR', 'Profil dan Pimpinan', 'institutional'],
            5 => ['Alur Penerbitan KKPR', 'Layanan KKPR', 'service:persetujuan-kkpr'],
            6 => ['Informasi Persetujuan Bangunan Gedung', 'Layanan PBG', 'service:persetujuan-bangunan-gedung-pbg'],
            7 => ['Persyaratan Pelayanan KKPR', 'Layanan KKPR', 'service:persetujuan-kkpr'],
            8 => ['Sistem dan Prosedur Pelayanan KKPR', 'Layanan KKPR', 'service:persetujuan-kkpr'],
            9 => ['Waktu, Biaya, dan Kontak Pelayanan KKPR', 'Layanan KKPR', 'service:persetujuan-kkpr'],
            10 => ['Penanganan Pengaduan Pelayanan KKPR', 'Layanan KKPR', 'service:persetujuan-kkpr'],
            11 => ['Dasar Hukum Pelayanan KKPR', 'Layanan KKPR', 'service:persetujuan-kkpr'],
            12 => ['Persyaratan Pelayanan PBG', 'Layanan PBG', 'service:persetujuan-bangunan-gedung-pbg'],
            13 => ['Waktu, Biaya, dan Kontak Pelayanan PBG', 'Layanan PBG', 'service:persetujuan-bangunan-gedung-pbg'],
            14 => ['Alur Pelayanan PBG', 'Layanan PBG', 'service:persetujuan-bangunan-gedung-pbg'],
            15 => ['Dasar Hukum dan Jaminan Pelayanan PBG', 'Layanan PBG', 'service:persetujuan-bangunan-gedung-pbg'],
        ];

        foreach ($infographics as $number => [$title, $category, $source]) {
            $file = sprintf('materi-resmi/infografis/materi-%02d.webp', $number);
            DB::table('media_assets')->updateOrInsert(
                ['file_path' => $file],
                ['title' => $title, 'caption' => 'Materi informasi resmi Dinas PUPR Kota Prabumulih',
                    'alt_text' => $title, 'mime_type' => 'image/webp', 'published_at' => $now,
                    'credit' => 'Dinas PUPR Kota Prabumulih', 'category' => $category,
                    'source_url' => $source, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $settings = [
            'email' => 'dispupr.kotaprabumulih@gmail.com',
            'phone' => '0821 2223 7969',
            'instagram' => '@dispupr_prabumulih',
            'homepage_kicker' => 'PORTAL RESMI DINAS PUPR KOTA PRABUMULIH',
            'homepage_title' => 'Infrastruktur hadir. <em>Pelayanan bergerak.</em>',
            'homepage_description' => 'Akses layanan penataan ruang dan bangunan gedung, ikuti kegiatan lapangan, serta temukan informasi resmi Dinas PUPR Kota Prabumulih.',
        ];
        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'group' => 'website', 'created_at' => $now, 'updated_at' => $now]
            );
        }

        DB::table('statistics')->updateOrInsert(
            ['label' => 'Indeks Kepuasan Masyarakat'],
            ['value' => '95.97', 'suffix' => '%', 'description' => 'Hasil IKM II Tahun 2026 berdasarkan materi resmi',
                'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now]
        );

        $knowledge = [
            ['Layanan KKPR', 'Permohonan KKPR diajukan melalui OSS RBA di oss.go.id. Waktu penyelesaian maksimal 20 hari. Kontak Dinas PUPR: dispupr.kotaprabumulih@gmail.com atau 0821 2223 7969.'],
            ['Layanan PBG', 'Permohonan PBG diajukan melalui SIMBG di simbg.pu.go.id. Waktu penyelesaian paling lama 28 hari kerja dan maksimal 5 kali pemeriksaan pemenuhan standar teknis.'],
            ['Kontak Pengaduan', 'Pengaduan pelayanan dapat disampaikan melalui email dispupr.kotaprabumulih@gmail.com, telepon 0821 2223 7969, atau Instagram @dispupr_prabumulih.'],
        ];
        foreach ($knowledge as [$title, $content]) {
            DB::table('ai_knowledge_sources')->updateOrInsert(
                ['title' => $title],
                ['source_type' => 'OFFICIAL_MATERIAL', 'content' => $content, 'is_enabled' => true,
                    'indexed_at' => $now, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        DB::table('news')->whereIn('slug', [
            'rangkaian-kegiatan-dinas-pupr-koordinasi-survei-pelayanan-lapangan',
            'konsultasi-publik-ii-rtrw-kota-prabumulih',
            'pelatihan-supervisor-k3-konstruksi-utama-jenjang-6',
            'survei-lapangan-monitoring-evaluasi-bidang-cipta-karya',
        ])->delete();
        DB::table('services')->whereIn('slug', ['persetujuan-kkpr', 'persetujuan-bangunan-gedung-pbg'])->delete();
        DB::table('media_assets')->where('file_path', 'like', 'materi-resmi/%')->delete();
        DB::table('ai_knowledge_sources')->where('source_type', 'OFFICIAL_MATERIAL')->delete();
    }
};
