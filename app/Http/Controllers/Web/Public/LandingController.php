<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;

class LandingController extends Controller
{
    public function index()
    {
        return view('public.landing');
    }
}
