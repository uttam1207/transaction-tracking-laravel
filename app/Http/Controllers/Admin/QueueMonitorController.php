<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueMonitorController extends Controller
{
    public function index()
    {
        $stats = $this->getQueueStats();
        $failed = DB::table('failed_jobs')->latest('failed_at')->limit(50)->get();
        $pending = DB::table('jobs')->orderBy('created_at')->limit(50)->get();

        return view('admin.queue.index', compact('stats', 'failed', 'pending'));
    }

    public function retry(string $uuid)
    {
        \Illuminate\Support\Facades\Artisan::call('queue:retry', ['id' => [$uuid]]);
        return back()->with('success', 'Job re-queued successfully.');
    }

    public function retryAll()
    {
        \Illuminate\Support\Facades\Artisan::call('queue:retry', ['id' => ['all']]);
        return back()->with('success', 'All failed jobs re-queued.');
    }

    public function deleteFailedJob(string $uuid)
    {
        \Illuminate\Support\Facades\Artisan::call('queue:forget', ['id' => $uuid]);
        return back()->with('success', 'Failed job removed.');
    }

    public function flushFailed()
    {
        \Illuminate\Support\Facades\Artisan::call('queue:flush');
        return back()->with('success', 'All failed jobs flushed.');
    }

    private function getQueueStats(): array
    {
        $pending  = DB::table('jobs')->count();
        $failed   = DB::table('failed_jobs')->count();

        $byQueue  = DB::table('jobs')
            ->select('queue', DB::raw('count(*) as total'))
            ->groupBy('queue')
            ->get()
            ->pluck('total', 'queue')
            ->toArray();

        return compact('pending', 'failed', 'byQueue');
    }
}
