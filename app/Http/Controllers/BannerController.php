<?php

namespace App\Http\Controllers;

class BannerController extends Controller
{
    public function first()
    {
        return view('banner.first');
    }

    public function second()
    {
        return view('banner.second');
    }

    public function third()
    {
        return view('banner.third');
    }
}
