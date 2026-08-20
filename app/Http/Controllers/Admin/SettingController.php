<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
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
        // Checkpoint: capture fraud detection state BEFORE saving
        $fraudWasEnabled = Setting::get('fraud_detection_enabled', '1') !== '0';

        // Checkbox fields per group — must be explicitly set to '0' when unchecked
        $checkboxFields = [
            'fraud'        => ['fraud_detection_enabled', 'auto_block_high_risk'],
            'security'     => ['require_2fa'],
            'notification' => ['email_alerts_enabled'],
        ];

        foreach ($checkboxFields[$group] ?? [] as $field) {
            if (!$request->boolean($field)) {
                Setting::updateOrCreate(['key' => $field], ['value' => '0', 'group' => $group]);
                Cache::forget("setting_{$field}");
            }
        }

        // Save all submitted scalar fields
        $skip = ['_token', '_method'];
        foreach ($request->except($skip) as $key => $value) {
            if (is_array($value)) continue; // skip files/arrays
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group]
            );
            Cache::forget("setting_{$key}");
        }

        // Log fraud detection toggle (only when the group is 'fraud' and the value actually changed)
        if ($group === 'fraud') {
            $fraudIsNowEnabled = Setting::get('fraud_detection_enabled', '1') !== '0';

            if ($fraudWasEnabled !== $fraudIsNowEnabled) {
                $action = $fraudIsNowEnabled ? 'enabled' : 'disabled';
                AuditLog::create([
                    'user_id'        => auth()->id(),
                    'event'          => 'fraud_detection_' . $action,
                    'auditable_type' => Setting::class,
                    'auditable_id'   => null,
                    'old_values'     => ['fraud_detection_enabled' => $fraudWasEnabled ? '1' : '0'],
                    'new_values'     => ['fraud_detection_enabled' => $fraudIsNowEnabled ? '1' : '0'],
                    'ip_address'     => $request->ip(),
                    'user_agent'     => $request->userAgent(),
                    'url'            => $request->fullUrl(),
                    'module'         => 'fraud',
                    'description'    => auth()->user()->name . ' ' . $action . ' the Fraud Detection Engine',
                ]);
            }
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
