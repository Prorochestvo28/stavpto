<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $activityLogs = $request->user()
            ->activityLogs()
            ->paginate(20)
            ->withQueryString();

        return view('settings.index', [
            'activityLogs' => $activityLogs,
        ]);
    }
}
