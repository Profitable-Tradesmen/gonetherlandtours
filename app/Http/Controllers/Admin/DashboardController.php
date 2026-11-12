<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'weekCount' => Enquiry::where('created_at', '>=', now()->startOfWeek())->count(),
            'newCount' => Enquiry::where('status', 'new')->count(),
            'ghlFailed' => Enquiry::where('ghl_status', 'failed')->count(),
            'recent' => Enquiry::latest()->take(8)->get(),
        ]);
    }
}
