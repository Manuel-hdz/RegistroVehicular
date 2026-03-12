<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use Illuminate\View\View;

class HumanResourcesController extends Controller
{
    public function index(): View
    {
        $totalPersonnel = Personnel::count();
        $activePersonnel = Personnel::where('active', true)->count();

        return view('hr.index', compact('totalPersonnel', 'activePersonnel'));
    }
}
