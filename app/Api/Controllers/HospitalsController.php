<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午3:09
 */

namespace App\Api\Controllers;


use App\Api\Transformers\HospitalTransformer;
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
        $hospital = Hospital::paginate(15);

        return $this->response->paginator($hospital, new HospitalTransformer());
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
}
