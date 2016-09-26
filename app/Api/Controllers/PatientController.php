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
use App\Appointment;
use App\Patient;
use App\User;

class PatientController extends BaseController
{
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
            return response()->json(['success' => ''], 204);
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

    public function all()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $allAppointment = Appointment::where('doctor_id', $user->id)->get();
        $patientIdList = Appointment::select('patient_id', 'count(*) as count')
            ->where('doctor_id', $user->id)
            ->where('patient_id', '!=', 'null')
            ->groupBy('patient_id')
//            ->distinct()
            ->get();

        return $patientIdList;

        foreach ($allAppointment as $item) {
            if (in_array($item->patient_id, $patientIdList)) {

            }
        }
    }
}
