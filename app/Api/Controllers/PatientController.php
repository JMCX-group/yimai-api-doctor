<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Requests\PhoneRequest;
use App\Api\Transformers\PatientTransformer;
use App\Patient;

class PatientController extends BaseController
{
    public function index()
    {
    }

    /**
     * @param PhoneRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function getInfoByPhone(PhoneRequest $request)
    {
        $patientInfo = Patient::select('id', 'phone', 'name', 'gender', 'birthday')
            ->where('phone', $request['phone'])
            ->get()
            ->toArray();

        if (Empty($patientInfo)) {
//            return $this->response->noContent();
            return response()->json(['success' => ''], 204); //给肠媳适配。。
        } else {
            $patientInfo[0]['birthday'] = $this->age($patientInfo[0]['birthday']);
            return $this->response->array($patientInfo, new PatientTransformer());
        }
    }

    /**
     * 根据生日计算年龄
     * 
     * @param $birthday
     * @return mixed
     */
    public function age($birthday)
    {
        $birthday = strtotime($birthday);
        $year = date('Y', $birthday);

        if (($month = (date('m') - date('m', $birthday))) < 0) {
            $year++;
        } else if ($month == 0 && date('d') - date('d', $birthday) < 0) {
            $year++;
        }

        return date('Y') - $year;
    }
}
