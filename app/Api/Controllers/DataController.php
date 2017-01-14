<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Requests\IdRequest;
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

    /**
     * 生成未来14天的排班数据
     *
     * @param IdRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function scheduling(IdRequest $request)
    {
        /**
         * 获取医生数据
         */
        $user = User::find($request['id']);

        /**
         * 获取固定排班和灵活排班数组：
         */
        $fixed = json_decode($user->admission_set_fixed, true);
        $flexible = json_decode($user->admission_set_flexible, true);
        $user->admission_set_flexible = $this->delOutdated($flexible);
        $user->save();

        /**
         * 生成基础数据结构：
         */
        $week = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
        $data = array();

        /**
         * 生成未来14天的数据:
         */
        for ($day = 0; $day < 14; $day++) {
            /**
             * 基础数据：今天周几，今天是几号，今天的数据结构
             */
            $tmpWeek = $week[date('w', strtotime('+' . $day . ' day'))];
            $tmpDate = date('Y-m-d', strtotime('+' . $day . ' day'));
            $tmpData = [
                'date' => $tmpDate,
                'am' => 'false',
                'pm' => 'false',
            ];

            /**
             * 先固定排班取值，如果都是true则不进行灵活排班取值
             */
            foreach ($fixed as $item) {
                if ($item['week'] == $tmpWeek) {
                    $tmpData['am'] = ($item['am'] == 'true' || $item['am'] == true) ? 'true' : 'false';
                    $tmpData['pm'] = ($item['pm'] == 'true' || $item['pm'] == true) ? 'true' : 'false';

                    break;
                }
            }

            /**
             * 如果不都是true则进行灵活排班取值
             * 只有'true' 或 true才需要进行赋值
             */
            if ($flexible != null) {
                if ($tmpData['am'] == 'false' || $tmpData['pm'] == 'false') {
                    foreach ($flexible as $item) {
                        if ($item['date'] == $tmpDate) {
                            if ($item['am'] == 'true' || $item['am'] == true) {
                                $tmpData['am'] = 'true';
                            }

                            if ($item['pm'] == 'true' || $item['pm'] == true) {
                                $tmpData['am'] = 'true';
                            }

                            break;
                        }
                    }
                }
            }

            array_push($data, $tmpData);
        }

        return response()->json(compact('data'));
    }

    /**
     * 删除过期时间
     *
     * @param $data
     * @return string
     */
    public function delOutdated($data)
    {
        $now = time();
        $newData = array();
        foreach ($data as $item) {
            if (strtotime($item['date']) > $now) {
                array_push($newData, $item);
            }
        }

        return json_encode($newData);
    }
}
