<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Appointment;
use App\User;

class DataController extends BaseController
{
    /**
     * 获取医院/医生/约诊单等数量
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function authColumn()
    {
        $data = [
            'hospital_count' => User::where('id', '>', 5)->groupBy('hospital_id')->get()->count(),
            'doctor_count' => User::where('id', '>', 5)->count(),
            'appointment_count' => Appointment::count(),
        ];

        return response()->json(compact('data'));
    }
}
