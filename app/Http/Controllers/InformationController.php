<?php

namespace App\Http\Controllers;

use App\Models\MapLocation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InformationController extends Controller
{
    /**
     * Display the campus information page.
     */
    public function index(Request $request): View
    {
        $communities = \App\Models\CommunityLink::where('is_active', true)->orderBy('name')->get();
        return view('information', compact('communities'));
    }
}
