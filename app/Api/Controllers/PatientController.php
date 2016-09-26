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
use App\FaceToFaceAdvice;
use App\Patient;
use App\User;
use Illuminate\Support\Facades\DB;

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

    /**
     * My patients.
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function all()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        //约诊数据：
        $patients = Appointment::select(
            DB::raw('patient_id as id, patient_name as name, 
            patient_gender as sex, patient_age as age, patient_phone as phone, patients.avatar,
            count(*) as appointment_count'))
            ->join('patients', 'appointments.patient_id', '=', 'patients.id')
            ->where('doctor_id', $user->id)
            ->where('patient_id', '!=', 'null')
            ->groupBy('patient_id')
            ->get();

        //患者数量：
        $patientCount = count($patients);

        //约诊数量&面诊数量：
        $appointmentCount = 0;
        $faceToFaceCount = 0;
        foreach ($patients as &$patient) {
            $appointmentCount += $patient->appointment_count;
            $count = FaceToFaceAdvice::select(DB::raw('count(*) as count'))
                ->where('phone', $patient['phone'])
                ->get();
            $count = (int)$count[0]['count'];
            $patient['face_to_face_count'] = $count;
            $faceToFaceCount += $count;
        }

        $data = [
            'patient_count' => $patientCount,
            'appointment_count' => $appointmentCount,
            'face_to_face_count' => $faceToFaceCount,
            'patient_list' => $patients
        ];

        return response()->json(compact('data'));
    }
}
