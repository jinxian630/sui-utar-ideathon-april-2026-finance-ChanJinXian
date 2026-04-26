<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Goal;
use App\Models\SavingsEntry;
use App\Models\User;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'totalUsers' => User::count(),
            'totalSavingsEntries' => SavingsEntry::count(),
            'totalGoals' => Goal::count(),
            'totalBadges' => Badge::count(),
        ]);
    }
}
