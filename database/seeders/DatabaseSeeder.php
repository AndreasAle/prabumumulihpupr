<?php

namespace Database\Seeders;

use App\Models\AiKnowledgeSource;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@pupr.test'],
            [
                'name' => 'Administrator PUPR',
                'password' => bcrypt('PrabuDemo!2026'),
                'role' => 'SUPER_ADMIN',
                'is_active' => true,
            ]
        );

        foreach ([
            ['Jam Pelayanan', 'Pelayanan kantor berlangsung Senin sampai Jumat mengikuti ketentuan jam kerja Pemerintah Kota Prabumulih. Konfirmasi perubahan jadwal melalui kanal kontak resmi.'],
            ['Pengaduan Infrastruktur', 'Pengaduan dapat dikirim melalui halaman Pengaduan. Lengkapi nomor telepon, lokasi, kategori, uraian, dan lampiran foto bila tersedia. Sistem akan menerbitkan nomor tiket.'],
            ['Dokumen Publik', 'Dokumen yang disetujui admin tersedia pada halaman Transparansi dan dapat dicari berdasarkan kata kunci atau kategori.'],
        ] as [$title, $content]) {
            AiKnowledgeSource::updateOrCreate(
                ['title' => $title],
                ['source_type' => 'FAQ', 'content' => $content, 'is_enabled' => true, 'indexed_at' => now()]
            );
        }
    }
}
