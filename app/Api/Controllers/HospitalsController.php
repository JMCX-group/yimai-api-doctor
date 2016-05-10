<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午3:09
 */

namespace App\Api\Controllers;

use App\Api\Transformers\HospitalTransformer;
use App\Api\Transformers\HospitalCityTransformer;
use App\Hospital;

class HospitalsController extends BaseController
{
    /**
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $hospitals = Hospital::paginate(15);

        return $this->response->paginator($hospitals, new HospitalTransformer());
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $hospital = Hospital::find($id);
        if (!$hospital) {
            return $this->response->errorNotFound('Hospital not found');
        }

        return $this->response->item($hospital, new HospitalTransformer());
    }

    /**
     * 在某个城市的医院
     *
     * @param $cityId
     * @return mixed
     */
    public function inCityHospital($cityId)
    {
        $hospitals = Hospital::select('id', 'name')
            ->where('city', $cityId)
            ->orderBy('three_a', 'desc')
            ->get();

        return $this->response->collection($hospitals, new HospitalCityTransformer());
    }

    /**
     * 通过名称模糊查询医院
     *
     * @param $data
     * @return \Dingo\Api\Http\Response
     */
    public function findHospital($data)
    {
        preg_match_all('/./u', $data, $newData);
        $newData = implode('%', $newData[0]);
        $newData = '%' . $newData . '%';

        $hospitals = Hospital::select('id', 'name')
            ->where('name', 'like', $newData)
            ->orderBy('three_a', 'desc')
            ->get();

        return $this->response->collection($hospitals, new HospitalCityTransformer());
    }
}
