<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\AppointmentMsg;
use App\Api\Transformers\AdmissionsMsgTransformer;
use App\User;
use Illuminate\Http\Request;

class AdmissionsMsgController extends BaseController
{
    /**
     * @return array|mixed
     */
    public function index()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $allMsg = AppointmentMsg::where('doctor_id', $user->id)
            ->whereIn('status', array('wait-2', 'wait-5', 'cancel-3', 'cancel-5', 'cancel-6'))
            ->orderBy('id', 'DESC')
            ->get();

        $retData = array();
        foreach ($allMsg as $item) {
            $text = AdmissionsMsgTransformer::transformerMsgList($item);

            if ($text && $text['text']) {
                array_push($retData, $text);
            }
        }

        return ['data' => $retData];
    }

    /**
     * 未读。
     *
     * @return array|mixed
     */
    public function newMessage()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $allMsg = AppointmentMsg::where('doctor_id', $user->id)
            ->whereIn('status', array('wait-2', 'wait-5', 'cancel-3', 'cancel-5', 'cancel-6'))
            ->where('doctor_read', 0)
            ->orderBy('id', 'DESC')
            ->get();

        $retData = array();
        foreach ($allMsg as $item) {
            $text = AdmissionsMsgTransformer::transformerMsgList($item);

            if ($text && $text['text']) {
                array_push($retData, $text);
            }
        }

        return ['data' => $retData];
    }

    /**
     * 已读状态更新。
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function readMessage(Request $request)
    {
        $msg = AppointmentMsg::find($request['id']);
        $msg->doctor_read = 1;
        $msg->save();

        return response()->json(['success' => ''], 204); //给肠媳适配。。
    }

    /**
     * All read.
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function allRead()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        AppointmentMsg::where('doctor_id', $user->id)->update(['doctor_read' => 1]);

        return response()->json(['success' => ''], 204);
    }
}
