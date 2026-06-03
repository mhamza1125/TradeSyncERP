<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $activities = Activity::with('causer')
            ->when($request->search, function ($q, $s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('subject_type', 'like', "%{$s}%");
            })
            ->when($request->causer, fn ($q, $c) => $q->where('causer_id', $c))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('activities.index', compact('activities'));
    }
}
