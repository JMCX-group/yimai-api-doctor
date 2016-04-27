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
    /**
     * Get all city and province.
     *
     * @return mixed
     */
    public function index()
    {
        $provinces = Province::all();
        $citys = City::all(array('id', 'name', 'province_id'));

        $data = [
            'provinces' => $provinces,
            'citys' => $citys
        ];

        return $this->response->array($data, new CityTransformer());
    }

    /**
     * Get all city and province, group by province.
     *
     * @return mixed
     */
    public function cityGroup()
    {
        $provinces = Province::all();
        $citys = City::all(array('id', 'name', 'province_id'))->groupBy('province_id');

        // Transformer.
        foreach ($citys as &$city) {
            foreach ($city as &$value) {
                unset($value['province_id']);
            }
        }

        $data = [
            'provinces' => $provinces,
            'citys' => $citys
        ];

        return $this->response->array($data, new CityTransformer());
    }
}
