<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Complaint;
use App\Models\Document;
use App\Models\MediaAsset;
use App\Models\News;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\Statistic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicController extends Controller
{
    public function home()
    {
        return view('public.home', [
            'statistics' => Statistic::orderBy('sort_order')->get(), 'projects' => Project::where('is_published', true)->where('is_featured', true)->latest()->take(3)->get(),
            'services' => Service::where('is_published', true)->orderBy('sort_order')->take(6)->get(), 'documents' => Document::where('is_public', true)->latest('published_at')->take(5)->get(),
            'news' => News::where('status', 'PUBLISHED')->latest('published_at')->take(4)->get(), 'agendas' => Agenda::where('status', 'PUBLISHED')->orderBy('start_datetime')->take(4)->get(),
            'institutionalMedia' => MediaAsset::where('source_url', 'institutional')->orderBy('file_path')->get(),
        ]);
    }

    public function listing(string $type, Request $request)
    {
        $map = ['berita' => [News::class, 'published_at'], 'layanan' => [Service::class, 'created_at'], 'proyek' => [Project::class, 'year'], 'transparansi' => [Document::class, 'year']];
        abort_unless(isset($map[$type]), 404);
        [$model,$sort] = $map[$type];
        $query = $model::query();
        if ($type === 'berita') {
            $query->where('status', 'PUBLISHED');
        } if ($type === 'layanan' || $type === 'proyek') {
            $query->where('is_published', true);
        } if ($type === 'transparansi') {
            $query->where('is_public', true);
        }
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(fn ($x) => $x->where($type === 'layanan' ? 'name' : 'title', 'like', "%$q%")->orWhere($type === 'layanan' ? 'summary' : 'description', 'like', "%$q%"));
        }
        $category = trim((string) $request->query('category', ''));
        if ($category !== '') {
            $query->where('category', $category);
        }

        $legacySlugs = match ($type) {
            'proyek' => ['program-penyelenggaraan-penataan-ruang', 'capaiankinerja', 'strategi-antisipatif-organisasi'],
            'layanan' => ['saran-dan-pengaduan', 'program-penyelenggaraan-penataan-ruang', 'contact-2'],
            'transparansi' => ['visimisi', 'tugasfungsi', 'capaiankinerja', 'strategi-antisipatif-organisasi', 'program-penyelenggaraan-penataan-ruang'],
            default => [],
        };
        $legacyPages = Page::where('status', 'PUBLISHED')->whereIn('slug', $legacySlugs)->get()->keyBy('slug');
        $fieldNews = $type === 'proyek'
            ? News::where('status', 'PUBLISHED')->latest('published_at')->get()
            : collect();

        return view('public.listing', [
            'type' => $type,
            'items' => $query->orderByDesc($sort)->paginate(9)->withQueryString(),
            'legacyPages' => $legacyPages,
            'fieldNews' => $fieldNews,
        ]);
    }

    public function detail(string $type, string $slug)
    {
        $map = ['berita' => News::class, 'layanan' => Service::class, 'proyek' => Project::class, 'transparansi' => Document::class];
        abort_unless(isset($map[$type]), 404);
        $item = $map[$type]::where('slug', $slug)->firstOrFail();
        $related = collect();
        if ($type === 'proyek') {
            $related = Project::where('is_published', true)->whereKeyNot($item->id)
                ->orderByRaw('CASE WHEN category = ? THEN 0 ELSE 1 END', [$item->category])
                ->latest('published_at')->take(3)->get();
        } elseif ($type === 'berita') {
            $related = News::where('status', 'PUBLISHED')->whereKeyNot($item->id)
                ->orderByRaw('CASE WHEN category = ? THEN 0 ELSE 1 END', [$item->category])
                ->latest('published_at')->take(3)->get();
        }

        $detailMedia = match ($type) {
            'berita' => MediaAsset::where('source_url', 'article:'.$item->slug)->orderBy('file_path')->get(),
            'layanan' => MediaAsset::where('source_url', 'service:'.$item->slug)->orderBy('file_path')->get(),
            default => collect(),
        };

        return view('public.detail', compact('type', 'item', 'related', 'detailMedia'));
    }

    public function page(string $slug)
    {
        abort_unless(in_array($slug, ['profil', 'visi-misi', 'tugas-fungsi', 'struktur-organisasi', 'kontak', 'galeri', 'agenda']), 404);
        $pageData = match ($slug) {
            'agenda' => Agenda::where('status', 'PUBLISHED')->orderBy('start_datetime')->get(),
            'galeri' => MediaAsset::where('mime_type', 'like', 'image/%')
                ->whereNotIn('mime_type', ['image/svg+xml'])
                ->where(fn ($query) => $query->whereNull('file_path')->orWhere('file_path', 'not like', 'materi-resmi/%'))
                ->latest('legacy_id')->take(30)->get(),
            default => collect(),
        };

        $legacyPages = collect();
        $videoData = collect();
        $officialMedia = collect();
        if ($slug === 'profil') {
            $legacyPages = Page::whereIn('slug', [
                'home-3', 'visimisi', 'tugasfungsi', 'contact-3', 'capaiankinerja',
                'strategi-antisipatif-organisasi',
            ])->get()->keyBy('slug');
        } elseif ($slug === 'galeri') {
            $videoData = MediaAsset::where('mime_type', 'like', 'video/%')->latest('published_at')->get();
            $officialMedia = MediaAsset::where('mime_type', 'image/jpeg')
                ->where('file_path', 'like', 'materi-resmi/%')
                ->where('source_url', 'like', 'article:%')
                ->orderBy('category')->orderBy('file_path')->get()->groupBy('category');
        }

        return view('public.page', compact('slug', 'pageData', 'legacyPages', 'videoData', 'officialMedia'));
    }

    public function legacyPage(string $slug)
    {
        $page = Page::where('status', 'PUBLISHED')->where('slug', $slug)->firstOrFail();

        return view('public.legacy-page', compact('page'));
    }

    public function archive()
    {
        return view('public.archive', [
            'pages' => Page::where('status', 'PUBLISHED')->orderBy('title')->get(),
            'mediaCount' => MediaAsset::count(),
        ]);
    }

    public function complaint()
    {
        return view('public.complaint');
    }

    public function storeComplaint(Request $request)
    {
        $data = $request->validate(['name' => 'required|max:120', 'email' => 'nullable|email', 'phone' => 'required|max:30', 'category' => 'required|max:80', 'location' => 'required|max:180', 'description' => 'required|min:20|max:5000', 'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120']);
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('complaints', 'public');
        }
        $data['ticket_number'] = 'PUPR-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        $complaint = Complaint::create($data);

        return back()->with('ticket', $complaint->ticket_number);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $results = [];
        if ($q) {
            $results['Berita'] = News::where('status', 'PUBLISHED')->where('title', 'like', "%$q%")->take(8)->get();
            $results['Layanan'] = Service::where('is_published', true)->where('name', 'like', "%$q%")->take(8)->get();
            $results['Proyek'] = Project::where('is_published', true)->where('title', 'like', "%$q%")->take(8)->get();
            $results['Dokumen'] = Document::where('is_public', true)->where('title', 'like', "%$q%")->take(8)->get();
        }

        return view('public.search', compact('q', 'results'));
    }
}
