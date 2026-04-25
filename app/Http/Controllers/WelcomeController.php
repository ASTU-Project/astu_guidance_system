<?php

namespace App\Http\Controllers;

use App\Models\MapLocation;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        $locations = MapLocation::query()
            ->select(['id', 'name', 'description', 'latitude', 'longitude', 'category', 'icon', 'image_url'])
            ->get();
        return view('welcome', compact('locations'));
    }
}
