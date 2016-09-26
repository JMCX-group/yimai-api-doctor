<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/9/26
 * Time: 下午6:49
 */

namespace App\Api\Controllers;

use App\Api\Transformers\Transformer;
use App\User;
use Illuminate\Http\Request;

class SearchController extends BaseController
{
    /**
     * Doctors.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function doctors(Request $request)
    {
        $idList = explode(',', $request['id_list']);
        $doctors = User::find($idList);

        $data = array();
        foreach ($doctors as $doctor){
            array_push($data, Transformer::searchDoctorTransform($doctor));
        }

        return response()->json(compact('data'));
    }
}