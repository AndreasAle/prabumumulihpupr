<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\AiKnowledgeSource;
use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\Document;
use App\Models\MediaAsset;
use App\Models\News;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Statistic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;
use Symfony\Component\Process\Process;

class AdminController extends Controller
{
    private array $modules = [
        'news' => News::class, 'pages' => Page::class, 'services' => Service::class,
        'projects' => Project::class, 'documents' => Document::class, 'agendas' => Agenda::class,
        'statistics' => Statistic::class, 'media' => MediaAsset::class, 'complaints' => Complaint::class,
        'users' => User::class, 'knowledge' => AiKnowledgeSource::class, 'audits' => AuditLog::class,
    ];

    public function dashboard()
    {
        return view('admin.dashboard', [
            'counts' => [
                'Berita terbit' => News::where('status', 'PUBLISHED')->count(),
                'Halaman publik' => Page::where('status', 'PUBLISHED')->count(),
                'Media tersimpan' => MediaAsset::count(),
                'Agenda aktif' => Agenda::where('status', 'PUBLISHED')->count(),
                'Pengaduan baru' => Complaint::where('status', 'NEW')->count(),
            ],
            'complaints' => Complaint::latest()->take(6)->get(),
            'recentAudits' => AuditLog::with('user')->latest()->take(6)->get(),
            'storageSize' => MediaAsset::sum('file_size'),
        ]);
    }

    public function index(string $module, Request $request)
    {
        $this->authorizeModule($module);
        $model = $this->model($module);
        $query = $model::query();
        if ($module === 'audits') {
            $query->with('user');
        }
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $field = match ($module) {
                'services' => 'name', 'complaints' => 'ticket_number', 'statistics' => 'label',
                'media', 'users', 'knowledge' => $module === 'users' ? 'name' : 'title',
                'audits' => 'description', default => 'title',
            };
            $query->where($field, 'like', "%{$q}%");
        }

        return view('admin.index', ['module' => $module, 'items' => $query->latest()->paginate(20)->withQueryString()]);
    }

    public function create(string $module)
    {
        $this->authorizeModule($module);
        abort_if(in_array($module, ['complaints', 'audits'], true), 404);

        return view('admin.form', ['module' => $module, 'item' => null, 'media' => MediaAsset::latest()->take(100)->get()]);
    }

    public function store(string $module, Request $request)
    {
        $this->authorizeModule($module);
        if ($module === 'media') {
            return $this->storeMedia($request);
        }
        $model = $this->model($module);
        $data = $this->validated($module, $request);
        $data = $this->prepareData($module, $data, null);
        $item = $model::create($data);
        $this->audit('CREATE', $module, $item, 'Menambahkan data baru.', $data);

        return redirect("/admin/{$module}")->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(string $module, int $id)
    {
        $this->authorizeModule($module);
        abort_if($module === 'audits', 404);

        return view('admin.form', [
            'module' => $module,
            'item' => $this->model($module)::findOrFail($id),
            'media' => MediaAsset::latest()->take(100)->get(),
        ]);
    }

    public function update(string $module, int $id, Request $request)
    {
        $this->authorizeModule($module);
        $item = $this->model($module)::findOrFail($id);
        $before = $item->toArray();
        $data = $this->prepareData($module, $this->validated($module, $request), $item);
        $item->update($data);
        $this->audit('UPDATE', $module, $item, 'Memperbarui data.', ['before' => $before, 'after' => $data]);

        if ($module === 'complaints' && $item->email && $request->boolean('notify_reporter')) {
            Mail::raw("Status laporan {$item->ticket_number}: {$item->status}\n\n{$item->public_response}", fn ($mail) => $mail->to($item->email)->subject('Pembaruan Pengaduan Dinas PUPR'));
            $item->update(['notified_at' => now()]);
        }

        return redirect("/admin/{$module}")->with('success', 'Perubahan berhasil disimpan.');
    }

    public function destroy(string $module, int $id)
    {
        $this->authorizeModule($module);
        abort_if(in_array($module, ['complaints', 'audits'], true), 403);
        $item = $this->model($module)::findOrFail($id);
        if ($module === 'media' && Storage::disk('public')->exists($item->file_path)) {
            Storage::disk('public')->delete($item->file_path);
        }
        $this->audit('DELETE', $module, $item, 'Menghapus data.', $item->toArray());
        $item->delete();

        return back()->with('success', 'Data berhasil dihapus.');
    }

    public function settings(Request $request)
    {
        $this->authorizeModule('settings');
        if ($request->isMethod('post')) {
            $data = $request->validate(['settings' => 'required|array', 'settings.*' => 'nullable|string|max:5000', 'logo_file'=>'nullable|image|max:4096', 'banner_file'=>'nullable|image|max:12288']);
            if ($request->hasFile('logo_file')) $data['settings']['site_logo'] = 'storage/'.$request->file('logo_file')->store('branding', 'public');
            if ($request->hasFile('banner_file')) $data['settings']['homepage_banner'] = 'storage/'.$request->file('banner_file')->store('branding', 'public');
            foreach ($data['settings'] as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => Str::before($key, '.') ?: 'website']);
            }
            $this->audit('UPDATE', 'settings', null, 'Memperbarui pengaturan portal.', array_keys($data['settings']));

            return back()->with('success', 'Pengaturan portal berhasil disimpan.');
        }

        return view('admin.settings', ['settings' => Setting::pluck('value', 'key')]);
    }

    public function exportComplaints()
    {
        $this->authorizeModule('complaints');
        $filename = 'pengaduan-pupr-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tiket', 'Tanggal', 'Nama', 'Telepon', 'Email', 'Kategori', 'Lokasi', 'Status', 'Laporan', 'Respons']);
            Complaint::latest()->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [$row->ticket_number, $row->created_at, $row->name, $row->phone, $row->email, $row->category, $row->location, $row->status, $row->description, $row->public_response]);
                }
            });
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function export(string $module)
    {
        $this->authorizeModule($module);
        abort_unless(isset($this->modules[$module]) && !in_array($module, ['audits','users'], true), 404);
        $model = $this->model($module);
        return response()->streamDownload(function () use ($model) {
            $handle=fopen('php://output','w'); $first=true;
            $model::query()->chunk(200, function($rows) use($handle,&$first){ foreach($rows as $row){ $data=$row->toArray(); if($first){fputcsv($handle,array_keys($data));$first=false;} fputcsv($handle,array_map(fn($v)=>is_array($v)?json_encode($v):$v,$data)); }});
            fclose($handle);
        }, $module.'-'.now()->format('Ymd-His').'.csv', ['Content-Type'=>'text/csv']);
    }

    public function backup()
    {
        $this->authorizeModule('backups');
        abort_unless(class_exists(ZipArchive::class), 500, 'Ekstensi ZIP belum aktif.');
        Storage::makeDirectory('backups');
        $name = 'backup-portal-pupr-'.now()->format('Ymd-His').'.zip';
        $path = Storage::path('backups/'.$name);
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFile(database_path('database.sqlite'), 'database/database.sqlite');
        foreach (Storage::disk('public')->allFiles() as $file) {
            $zip->addFile(Storage::disk('public')->path($file), 'media/'.$file);
        }
        $zip->close();
        $this->audit('BACKUP', 'backups', null, 'Membuat backup database dan media.');

        return response()->download($path, $name);
    }

    public function preview(string $module, int $id)
    {
        $this->authorizeModule($module);
        $item = $this->model($module)::findOrFail($id);
        $type = ['news' => 'berita', 'services' => 'layanan', 'projects' => 'proyek', 'documents' => 'transparansi'][$module] ?? null;
        if ($type) {
            return view('public.detail', ['type' => $type, 'item' => $item, 'related' => collect()]);
        }
        if ($module === 'pages') {
            return view('public.legacy-page', ['page' => $item]);
        }
        abort(404);
    }

    private function storeMedia(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp,gif,mp4,webm,pdf|max:51200',
            'title' => 'required|max:200', 'alt_text' => 'nullable|max:250', 'caption' => 'nullable|max:500',
            'credit' => 'nullable|max:200', 'category' => 'nullable|max:100',
        ]);
        $file = $request->file('file');
        $path = $file->store('media/'.now()->format('Y/m'), 'public');
        $this->compressImage(Storage::disk('public')->path($path), (string)$file->getMimeType());
        $this->compressVideo(Storage::disk('public')->path($path), (string)$file->getMimeType());
        $dimensions = str_starts_with((string) $file->getMimeType(), 'image/') ? @getimagesize(Storage::disk('public')->path($path)) : null;
        $asset = MediaAsset::create([
            ...collect($data)->except('file')->all(), 'mime_type' => $file->getMimeType(), 'file_path' => $path,
            'file_size' => Storage::disk('public')->size($path), 'width' => $dimensions[0] ?? null, 'height' => $dimensions[1] ?? null,
            'published_at' => now(), 'uploaded_by' => auth()->id(),
        ]);
        $this->audit('UPLOAD', 'media', $asset, 'Mengunggah media baru.');

        return redirect('/admin/media')->with('success', 'Media berhasil diunggah dan divalidasi.');
    }

    public function editorUpload(Request $request)
    {
        $this->authorizeModule('media');
        $data = $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:10240',
            'alt_text' => 'nullable|string|max:250',
        ]);
        $file = $data['file'];
        $path = $file->store('media/editor/'.now()->format('Y/m'), 'public');
        $this->compressImage(Storage::disk('public')->path($path), (string) $file->getMimeType());
        $dimensions = @getimagesize(Storage::disk('public')->path($path));
        $asset = MediaAsset::create([
            'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'alt_text' => $data['alt_text'] ?? null,
            'mime_type' => $file->getMimeType(),
            'file_path' => $path,
            'file_size' => Storage::disk('public')->size($path),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'published_at' => now(),
            'uploaded_by' => auth()->id(),
            'category' => 'Editor',
        ]);
        $this->audit('UPLOAD', 'media', $asset, 'Mengunggah gambar melalui editor konten.');

        return response()->json(['url' => $asset->url, 'alt' => $asset->alt_text ?: $asset->title]);
    }

    private function compressImage(string $path, string $mime): void
    {
        if (!extension_loaded('gd') || !in_array($mime, ['image/jpeg','image/png','image/webp'], true)) return;
        $info=@getimagesize($path); if(!$info) return; [$w,$h]=$info; $max=2400;
        $ratio=min(1,$max/max($w,$h)); $nw=(int)round($w*$ratio); $nh=(int)round($h*$ratio);
        $source=match($mime){'image/jpeg'=>@imagecreatefromjpeg($path),'image/png'=>@imagecreatefrompng($path),default=>@imagecreatefromwebp($path)}; if(!$source)return;
        $target=imagecreatetruecolor($nw,$nh); if($mime==='image/png'){imagealphablending($target,false);imagesavealpha($target,true);} imagecopyresampled($target,$source,0,0,0,0,$nw,$nh,$w,$h);
        match($mime){'image/jpeg'=>imagejpeg($target,$path,82),'image/png'=>imagepng($target,$path,7),default=>imagewebp($target,$path,82)}; imagedestroy($source);imagedestroy($target);
    }

    private function compressVideo(string $path, string $mime): void
    {
        if (!in_array($mime, ['video/mp4','video/webm'], true)) return;
        $ffmpeg = PHP_OS_FAMILY === 'Windows' ? 'C:\\ffmpeg\\bin\\ffmpeg.exe' : 'ffmpeg';
        $temp = $path.'.compressed.'.pathinfo($path, PATHINFO_EXTENSION);
        $args = $mime === 'video/mp4'
            ? [$ffmpeg,'-y','-i',$path,'-vf','scale=min(1920\\,iw):-2','-c:v','libx264','-crf','27','-preset','medium','-c:a','aac','-b:a','128k','-movflags','+faststart',$temp]
            : [$ffmpeg,'-y','-i',$path,'-vf','scale=min(1920\\,iw):-2','-c:v','libvpx-vp9','-crf','35','-b:v','0','-c:a','libopus','-b:a','96k',$temp];
        try {
            $process = new Process($args); $process->setTimeout(600); $process->run();
            if ($process->isSuccessful() && is_file($temp) && filesize($temp) < filesize($path)) { unlink($path); rename($temp,$path); }
            elseif (is_file($temp)) unlink($temp);
        } catch (\Throwable) { if (is_file($temp)) @unlink($temp); }
    }

    private function prepareData(string $module, array $data, mixed $item): array
    {
        foreach (['content', 'description'] as $richField) {
            if (isset($data[$richField])) {
                $data[$richField] = $this->sanitizeRichHtml((string) $data[$richField]);
            }
        }
        $uploads = [
            'news' => ['thumbnail_file', 'thumbnail', 'covers'],
            'pages' => ['featured_image_file', 'featured_image', 'covers'],
            'projects' => ['featured_image_file', 'featured_image', 'projects'],
            'documents' => ['document_file', 'file_path', 'documents'],
        ];
        if (isset($uploads[$module])) {
            [$input, $column, $directory] = $uploads[$module];
            if (request()->hasFile($input)) {
                $file = request()->file($input);
                $path = $file->store($directory.'/'.now()->format('Y/m'), 'public');
                if ($module !== 'documents') {
                    $this->compressImage(Storage::disk('public')->path($path), (string) $file->getMimeType());
                    $data[$column] = Storage::disk('public')->url($path);
                } else {
                    $data[$column] = $path;
                    $data['file_type'] = strtoupper($file->getClientOriginalExtension());
                    $data['file_size'] = $this->humanFileSize(Storage::disk('public')->size($path));
                }
            }
            unset($data[$input]);
        }
        if (isset($data['title']) || isset($data['name'])) {
            $base = Str::slug($data['title'] ?? $data['name']);
            $data['slug'] = $item?->slug ?: $base.'-'.Str::lower(Str::random(4));
        }
        if (isset($data['status']) && $data['status'] === 'PUBLISHED' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        if (isset($data['publish_status'])) {
            $published = $data['publish_status'] === 'PUBLISHED';
            if ($module === 'services' || $module === 'projects') $data['is_published'] = $published;
            if ($module === 'documents') $data['is_public'] = $published;
            if ($module === 'agendas') $data['status'] = $data['publish_status'];
        }
        if ($module === 'users' && empty($data['password'])) unset($data['password']);

        return $data;
    }

    private function humanFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 1).' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 1).' KB';
        return $bytes.' B';
    }

    private function sanitizeRichHtml(string $html): string
    {
        $html = strip_tags($html, '<p><br><h2><h3><h4><strong><b><em><i><u><s><ul><ol><li><blockquote><a><figure><figcaption><img><hr><table><thead><tbody><tr><th><td>');
        $html = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/iu', '', $html) ?? $html;
        $html = preg_replace('/\s+(href|src)\s*=\s*(["\'])\s*(javascript:|data:text\/html)[^"\']*\2/iu', '', $html) ?? $html;
        $html = preg_replace('/<(script|style|iframe|object|embed)[^>]*>.*?<\/\1>/isu', '', $html) ?? $html;

        return trim($html);
    }

    private function validated(string $m, Request $r): array
    {
        $seo = ['seo_title' => 'nullable|max:200', 'seo_description' => 'nullable|max:500', 'seo_image' => 'nullable|max:500', 'scheduled_at' => 'nullable|date'];
        return match ($m) {
            'news' => $r->validate([...$seo, 'title' => 'required|max:200', 'category' => 'required|max:100', 'excerpt' => 'nullable|max:500', 'content' => 'required', 'thumbnail' => 'nullable|max:500', 'thumbnail_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240', 'author' => 'required|max:120', 'status' => 'required|in:DRAFT,REVIEW,SCHEDULED,PUBLISHED,ARCHIVED', 'published_at' => 'nullable|date']),
            'pages' => $r->validate([...$seo, 'title' => 'required|max:200', 'content' => 'required', 'excerpt' => 'nullable|max:500', 'featured_image' => 'nullable|max:500', 'featured_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240', 'status' => 'required|in:DRAFT,REVIEW,SCHEDULED,PUBLISHED,ARCHIVED', 'published_at' => 'nullable|date']),
            'services' => $r->validate([...$seo, 'name' => 'required|max:200', 'category' => 'required|max:100', 'summary' => 'required|max:500', 'description' => 'nullable', 'processing_time' => 'nullable|max:200', 'cost_information' => 'nullable|max:200', 'officer_contact' => 'nullable|max:200', 'online_url' => 'nullable|url', 'publish_status' => 'required|in:DRAFT,REVIEW,SCHEDULED,PUBLISHED,ARCHIVED', 'sort_order' => 'nullable|integer']),
            'projects' => $r->validate([...$seo, 'title' => 'required|max:200', 'category' => 'required|max:100', 'location' => 'required|max:250', 'district' => 'nullable|max:150', 'year' => 'required|digits:4', 'description' => 'required', 'progress_percentage' => 'required|integer|min:0|max:100', 'status' => 'required|in:PLANNED,ONGOING,COMPLETED,DELAYED', 'featured_image' => 'nullable|max:500', 'featured_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240', 'is_featured' => 'nullable|boolean', 'publish_status' => 'required|in:DRAFT,REVIEW,SCHEDULED,PUBLISHED,ARCHIVED']),
            'documents' => $r->validate([...$seo, 'title' => 'required|max:200', 'category' => 'required|max:100', 'year' => 'required|digits:4', 'description' => 'nullable', 'external_url' => 'nullable|url', 'document_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:51200', 'file_type' => 'nullable|max:30', 'file_size' => 'nullable|max:30', 'publish_status' => 'required|in:DRAFT,REVIEW,SCHEDULED,PUBLISHED,ARCHIVED', 'published_at' => 'nullable|date']),
            'agendas' => $r->validate([...$seo, 'title' => 'required|max:200', 'description' => 'nullable', 'start_datetime' => 'required|date', 'end_datetime' => 'nullable|date|after_or_equal:start_datetime', 'location' => 'required|max:250', 'publish_status' => 'required|in:DRAFT,REVIEW,SCHEDULED,PUBLISHED,ARCHIVED']),
            'statistics' => $r->validate(['label' => 'required|max:150', 'value' => 'required|max:30', 'suffix' => 'nullable|max:20', 'description' => 'nullable|max:300', 'icon' => 'nullable|max:100', 'sort_order' => 'nullable|integer']),
            'media' => $r->validate(['title' => 'required|max:200', 'alt_text' => 'nullable|max:250', 'caption' => 'nullable|max:500', 'credit' => 'nullable|max:200', 'category' => 'nullable|max:100']),
            'complaints' => $r->validate(['status' => 'required|in:NEW,VERIFIED,IN_PROCESS,RESOLVED,REJECTED', 'admin_notes' => 'nullable|max:2000', 'public_response' => 'nullable|max:3000']),
            'users' => $r->validate(['name' => 'required|max:150', 'email' => 'required|email|max:200|unique:users,email,'.($r->route('id') ?? 'NULL'), 'password' => ($r->route('id') ? 'nullable' : 'required').'|min:10', 'role' => 'required|in:SUPER_ADMIN,ADMIN,CONTENT_EDITOR,SERVICE_EDITOR,TRANSPARENCY_EDITOR,COMPLAINT_OFFICER', 'is_active' => 'required|boolean']),
            'knowledge' => $r->validate(['title' => 'required|max:200', 'source_type' => 'required|max:80', 'content' => 'required', 'is_enabled' => 'required|boolean']),
            default => [],
        };
    }

    private function model(string $module): string
    {
        abort_unless(isset($this->modules[$module]), 404);
        return $this->modules[$module];
    }

    private function authorizeModule(string $module): void
    {
        $role = request()->user()->role;
        if ($role === 'SUPER_ADMIN' || ($role === 'ADMIN' && ! in_array($module, ['users', 'backups'], true))) return;
        $allowed = [
            'CONTENT_EDITOR' => ['news', 'pages', 'media', 'agendas', 'statistics'],
            'SERVICE_EDITOR' => ['services', 'projects', 'agendas', 'media'],
            'TRANSPARENCY_EDITOR' => ['documents', 'pages', 'media'],
            'COMPLAINT_OFFICER' => ['complaints'],
        ];
        abort_unless(in_array($module, $allowed[$role] ?? [], true), 403, 'Anda tidak memiliki akses ke modul ini.');
    }

    private function audit(string $action, string $module, mixed $item, string $description, mixed $changes = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(), 'action' => $action, 'module' => $module,
            'record_type' => $item ? get_class($item) : null, 'record_id' => $item?->id,
            'description' => $description, 'changes' => $changes, 'ip_address' => request()->ip(),
        ]);
    }
}
