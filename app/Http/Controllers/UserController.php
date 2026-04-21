<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display the user's details.
     */
    public function index(Request $request)
    {
        return view('user', [
            'user' => $request->user(),
        ]);
    }
}
