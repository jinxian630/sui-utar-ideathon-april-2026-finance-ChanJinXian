<?php

namespace App\Http\Controllers;

class RegisterController extends Controller
{
    public function showRegister()
    {
        return view('auth.register'); // assuming there's a view named auth.register
    }
}
