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
use Illuminate\Http\Request;

class HospitalsController extends BaseController
{
    /**
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $hospitals = Hospital::paginate(100);

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
            ->where('city_id', $cityId)
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

    /**
     * @param Request $request
     * @return array
     */
    public function findHospital_provinces(Request $request)
    {
        $data = [
            'field' => isset($request['field']) && !empty($request['field']) ? $request['field'] : false,
            'province_id' => isset($request['province_id']) && !empty($request['province_id']) ? $request['province_id'] : false,
            'city_id' => isset($request['city_id']) && !empty($request['city_id']) ? $request['city_id'] : false
        ];

        $hospitals = Hospital::searchHospital_provinces($data['field'], $data['province_id'], $data['city_id']);

        /**
         * 排序/分组:
         */
        $newHospitals = array();
        $provinces = array();
        $citys = array();
        $cityIdList = array();
        $provinceIdList = array();
        foreach ($hospitals as $hospital) {
            $this->groupByProvinces($hospital, $provinces, $provinceIdList);
            $this->groupByCitys($hospital, $citys, $cityIdList);
            $this->groupByHospitals($hospital, $newHospitals);
        }
        
        /**
         * 把医院数据格式特殊处理:
         */
        if (isset($request['format']) && $request['format'] == 'android') {
            $newHospital = array();
            foreach ($newHospitals as $key => $val) {
                $newCityList = [
                    'province_id' => $key,
                    'data' => []
                ];
                foreach ($val as $keyItem => $valItem) {
                    $newHospitalList = [
                        'city_id' => $keyItem,
                        'data' => $valItem
                    ];
                    array_push($newCityList['data'], $newHospitalList);
                }
                array_push($newHospital, $newCityList);
            }

            $newHospitals = $newHospital;
        }

        return [
            'provinces' => $provinces,
            'citys' => $citys,
            'hospitals' => $newHospitals,
        ];
    }

    /**
     * @param $hospital
     * @param $provinces
     * @param $provinceIdList
     */
    public function groupByProvinces($hospital, &$provinces, &$provinceIdList)
    {
        if (!in_array($hospital->province_id, $provinceIdList)) {
            array_push($provinceIdList, $hospital->province_id);
            array_push(
                $provinces,
                ['id' => $hospital->province_id, 'name' => $hospital->province]
            );
        }
    }

    /**
     * @param $hospital
     * @param $citys
     * @param $cityIdList
     */
    public function groupByCitys($hospital, &$citys, &$cityIdList)
    {
        if (!in_array($hospital->city_id, $cityIdList)) {
            array_push($cityIdList, $hospital->city_id);
            if (isset($citys[$hospital->province_id])) {
                array_push(
                    $citys[$hospital->province_id],
                    ['id' => $hospital->city_id, 'name' => $hospital->city]
                );
            } else {
                $citys[$hospital->province_id] = [
                    ['id' => $hospital->city_id, 'name' => $hospital->city]
                ];
            }
        }
    }

    /**
     * @param $hospital
     * @param $newHospitals
     */
    public function groupByHospitals($hospital, &$newHospitals)
    {
        if (isset($newHospitals[$hospital->province_id]) && isset($newHospitals[$hospital->province_id][$hospital->city_id])) {
            array_push(
                $newHospitals[$hospital->province_id][$hospital->city_id],
                ['id' => $hospital->id, 'name' => $hospital->name, 'address' => $hospital->address,
                    'province_id' => $hospital->province_id, 'city_id' => $hospital->city_id]
            );
        } else {
            $newHospitals[$hospital->province_id][$hospital->city_id] = [
                ['id' => $hospital->id, 'name' => $hospital->name, 'address' => $hospital->address,
                    'province_id' => $hospital->province_id, 'city_id' => $hospital->city_id]
            ];
        }
    }
}
