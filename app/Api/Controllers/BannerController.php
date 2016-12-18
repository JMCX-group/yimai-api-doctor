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
    public function index()
    {
        $domain = \Config::get('constants.DOMAIN');
        $data = array();
//        $banners = Banner::where('status', '1')->orderBy('location')->get();
//        foreach ($banners as $banner) {
//            $tmp = [
//                'focus_img_url' => $domain . $banner->focus_img_url,
//                'content_url' => $domain . '/banner/first'
//            ];
//
//            array_push($data, $tmp);
//        }
        $tmp1 = [
            'focus_img_url' => $domain . '/banner/20161218205430.png',
            'content_url' => $domain . '/banner/first'
        ];
        $tmp2 = [
            'focus_img_url' => $domain . '/banner/20161218205430.png',
            'content_url' => $domain . '/banner/second'
        ];
        $tmp3 = [
            'focus_img_url' => $domain . '/banner/20161218205430.png',
            'content_url' => $domain . '/banner/third'
        ];

        array_push($data, $tmp1);
        array_push($data, $tmp2);
        array_push($data, $tmp3);

        return response()->json(compact('data'));
    }
}
