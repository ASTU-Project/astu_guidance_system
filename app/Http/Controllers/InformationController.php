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
        return view('information');
    }
}
