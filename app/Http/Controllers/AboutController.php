<?php

namespace App\Http\Controllers;

class AboutController extends Controller
{
    public function contactUs()
    {
        return view('about.contact_us');
    }

    public function introduction()
    {
        return view('about.introduction');
    }

    public function lawyer()
    {
        return view('about.lawyer');
    }
}
