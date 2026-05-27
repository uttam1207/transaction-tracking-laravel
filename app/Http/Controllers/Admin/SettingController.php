<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index(string $group = 'general')
    {
        $settings = Setting::byGroup($group)->get()->keyBy('key');
        $groups = ['general', 'smtp', 'notification', 'theme', 'api', 'currency'];
        return view('admin.settings.index', compact('settings', 'group', 'groups'));
    }

    public function update(Request $request, string $group)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group]
            );
            Cache::forget("setting_{$key}");
        }

        // Handle file uploads
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('settings', 'public');
            Setting::set('app_logo', $path);
        }

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('settings', 'public');
            Setting::set('app_favicon', $path);
        }

        return redirect()->back()->with('success', 'Settings saved successfully.');
    }

    public function testSmtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            \Illuminate\Support\Facades\Mail::raw('Test email from Transaction Monitor', function ($m) use ($request) {
                $m->to($request->email)->subject('SMTP Test');
            });
            return response()->json(['success' => true, 'message' => 'Test email sent successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()]);
        }
    }
}
