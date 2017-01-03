<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Banner;

class BannerController extends BaseController
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $domain = \Config::get('constants.DOMAIN');
        $data = array();
        $banners = Banner::where('d_or_p', 'd')
            ->where('location', '!=', '')
            ->orderBy('location')->get();
        foreach ($banners as $banner) {
            $tmp = [
                'focus_img_url' => $banner->focus_img_url,
                'content_url' => $domain . '/banner/' . $banner->id
            ];

            array_push($data, $tmp);
        }

        return response()->json(compact('data'));
    }
}
