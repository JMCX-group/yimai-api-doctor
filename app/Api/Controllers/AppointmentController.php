<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Requests\AppointmentRequest;
use App\Appointment;
use App\User;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Tymon\JWTAuth\Exceptions\JWTException;

class AppointmentController extends BaseController
{
    public function index()
    {

    }

    public function store(AppointmentRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $data = [
            'locums_id' => $user->id,
            'patient_name' => $request['name'],
            'patient_phone' => $request['phone'],
            'patient_gender' => $request['gender'],
            'patient_age' => $request['age'],
            'patient_history' => $request['history'],
            'doctor_id' => $request['doctor'],
            'visit_time' => $request['time'],
            'am_pm' => $request['am_or_pm'],
        ];

        try {
            $appointment = Appointment::create($data);
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }

        return ['id' => $appointment['id']];
    }

    public function updateImg(Request $request)
    {
        $appointment = Appointment::find($request['id']);
        $imgUrl = $this->saveImg($appointment->id, $request->file('img'));

        if (empty($appointment->patient_imgs)) {
            $appointment->patient_imgs .= $imgUrl;
        } else {
            $appointment->patient_imgs = $imgUrl;
        }

        return ['url' => $imgUrl];
    }

    public function update(AppointmentRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        if (isset($request['name']) && !empty($request['name'])) {
            $user->name = $request['name'];
        }
        if (isset($request['head_img']) && !empty($request['head_img'])) {
            $user->avatar = $this->avatar($user->id, $request->file('head_img'));
        }
        if (isset($request['sex']) && !empty($request['sex'])) {
            // 1:男; 0:女
            $user->gender = $request['sex'];
        }
        if (isset($request['province']) && !empty($request['province'])) {
            $user->province_id = $request['province'];
        }
        if (isset($request['city']) && !empty($request['city'])) {
            $user->city_id = $request['city'];
        }
        if (isset($request['hospital']) && !empty($request['hospital'])) {
            $hospitalId = $request['hospital'];
            if (!is_numeric($request['hospital'])) {
                $hospitalId = $this->createNewHospital($request);
            }
            $user->hospital_id = $hospitalId;
        }
        if (isset($request['department']) && !empty($request['department'])) {
            $user->dept_id = $request['department'];
        }
        if (isset($request['job_title']) && !empty($request['job_title'])) {
            $user->title = $request['job_title'];
        }
        if (isset($request['college']) && !empty($request['college'])) {
            $user->college_id = $request['college'];
        }
        if (isset($request['ID_number']) && !empty($request['ID_number'])) {
            $user->id_num = $request['ID_number'];
        }
        if (isset($request['tags']) && !empty($request['tags'])) {
            $user->tag_list = $request['tags'];
        }
        if (isset($request['personal_introduction']) && !empty($request['personal_introduction'])) {
            $user->profile = $request['personal_introduction'];
        }

        // Generate dp code.
        if (empty($user->dp_code) && !empty($user->dept_id)) {
            $user->dp_code = User::generateDpCode($user->dept_id);
        }

        try {
            if ($user->save()) {
                return $this->response->item($user, new UserTransformer());
            } else {
                return $this->response->error('unknown error', 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function saveImg($appointmentId, $imgFile)
    {
        //TODO debug 
        $nowYear = date('Y');
        $nowMonth = date('m');
        $destinationPath = 'uploads/case-history/' . $nowYear . '/' . $nowMonth . '/'; 
        $filename = $appointmentId . '_' . $imgFile->getClientOriginalName();
        $imgFile->move($destinationPath, $filename);
        $fullPath = $destinationPath.$filename;
        $newPath = str_replace($appointmentId . '_', $appointmentId . '_thumb_', $filename);

        Image::make($filename)->fit(200)->save($newFilename);

        return '/' . $newFilename;
    }
}
