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
use App\City;
use App\Hospital;

/**
 * Class HospitalsController
 * @package App\Api\Controllers
 */
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
     * @return \Dingo\Api\Http\Response|void
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
     * In a city hospital.
     *
     * @param $cityId
     * @return \Dingo\Api\Http\Response
     */
    public function inCityHospital($cityId)
    {
        $cityName = City::find($cityId);
        $hospitals = Hospital::select('id', 'name')->where('city', '=', $cityName->name)->get();

        return $this->response->collection($hospitals, new HospitalCityTransformer());
    }
}
