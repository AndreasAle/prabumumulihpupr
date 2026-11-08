<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\{News, Page, Service, Project, Document, Agenda};

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cms:publish-scheduled', function () {
    $count = 0;
    foreach ([News::class, Page::class, Service::class, Project::class, Document::class, Agenda::class] as $model) {
        $items = $model::where('publish_status', 'SCHEDULED')->where('scheduled_at', '<=', now())->get();
        foreach ($items as $item) {
            $data = ['publish_status' => 'PUBLISHED'];
            if ($item instanceof News || $item instanceof Page) $data['status'] = 'PUBLISHED';
            if ($item instanceof Service || $item instanceof Project) $data['is_published'] = true;
            if ($item instanceof Document) $data['is_public'] = true;
            if ($item instanceof Agenda) $data['status'] = 'PUBLISHED';
            if (isset($item->published_at) && !$item->published_at) $data['published_at'] = now();
            $item->update($data); $count++;
        }
    }
    $this->info("{$count} konten terjadwal diterbitkan.");
})->purpose('Menerbitkan konten CMS yang telah mencapai jadwal');

Schedule::command('cms:publish-scheduled')->everyMinute()->withoutOverlapping();
