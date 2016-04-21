<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Transformers\CityTransformer;
use App\City;
use App\Province;

class CityController extends BaseController
{
    public function index()
    {
        $provinces = Province::all();
        $citys =City::all();

        $data = [
            'provinces' => $provinces,
            'citys' => $citys
        ];

        return $this->response->array($data, new CityTransformer());
    }
}