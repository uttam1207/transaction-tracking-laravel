<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ASDairyErpService;
use Illuminate\Http\Request;

class ReportCenterController extends Controller
{
    public function center(ASDairyErpService $erpService)
    {
        $summary = $erpService->getExecutiveSummary();
        return view('admin.reports.center', compact('summary'));
    }
}
