<?php

namespace App\Http\Controllers;

use App\Banner;

class BannerController extends Controller
{
    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getBannerContent($id)
    {
        $data = Banner::find($id);

        return view('banner.index', compact('data'));
    }
}
