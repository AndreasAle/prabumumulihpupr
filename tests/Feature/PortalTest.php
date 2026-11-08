<?php

namespace Tests\Feature;

use App\Models\AiKnowledgeSource;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_seeded_portal(): void
    {
        $this->seed();
        $this->get('/')->assertOk()->assertSee('Infrastruktur hadir')->assertSee('hero-brand');
    }

    public function test_public_complaint_submission_returns_ticket(): void
    {
        $this->post('/pengaduan', ['name' => 'Warga Uji', 'phone' => '08123456789', 'category' => 'Drainase', 'location' => 'Jalan Contoh', 'description' => 'Saluran drainase perlu diperiksa karena terjadi genangan.'])->assertSessionHas('ticket');
        $this->assertDatabaseCount('complaints', 1);
        $this->assertSame('NEW', Complaint::first()->status);
    }

    public function test_admin_is_protected_and_can_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $user = User::factory()->create(['password' => bcrypt('secret-pass'), 'role' => 'SUPER_ADMIN']);
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'secret-pass'])->assertRedirect('/admin');
        $this->actingAs($user)->get('/admin')->assertOk()->assertSee('Ringkasan Website');
    }

    public function test_mock_assistant_uses_only_approved_knowledge(): void
    {
        AiKnowledgeSource::create(['title' => 'Jam Pelayanan', 'source_type' => 'FAQ', 'content' => 'Pelayanan tersedia hari kerja.', 'is_enabled' => true]);
        $this->postJson('/asisten', ['message' => 'jam pelayanan'])->assertOk()->assertJsonFragment(['answer' => 'Pelayanan tersedia hari kerja. Sumber: Jam Pelayanan.']);
        $this->postJson('/asisten', ['message' => 'berapa harga rumah'])->assertOk()->assertJsonPath('answer', fn ($answer) => str_contains($answer, 'belum tersedia'));
    }

    public function test_editor_cannot_access_unrelated_module(): void
    {
        $user = User::factory()->create(['role' => 'CONTENT_EDITOR']);
        $this->actingAs($user)->get('/admin/news')->assertOk();
        $this->actingAs($user)->get('/admin/documents')->assertForbidden();
    }

    public function test_redesigned_public_pages_render(): void
    {
        $this->seed();

        foreach (['/profil', '/visi-misi', '/tugas-fungsi', '/struktur-organisasi', '/layanan', '/proyek', '/transparansi', '/berita', '/galeri', '/agenda', '/kontak', '/pengaduan'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/berita')->assertSee('Rangkaian Kegiatan Dinas PUPR');
    }

    public function test_search_renders_empty_results_and_matching_content(): void
    {
        $this->seed();

        $this->get('/cari')->assertOk()->assertSee('Masukkan kata kunci.');
        $this->get('/cari?q=Koordinasi')->assertOk()->assertSee('Rangkaian Kegiatan Dinas PUPR');
        $this->get('/cari?q=tidak-ada-hasil-sama-sekali')->assertOk()->assertSee('Informasi tidak ditemukan.');
    }
}
