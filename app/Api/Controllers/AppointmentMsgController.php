<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Transformers\AppointmentMsgTransformer;
use App\AppointmentMsg;
use App\User;
use Illuminate\Http\Request;

class AppointmentMsgController extends BaseController
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

        $allMsg = AppointmentMsg::where('locums_id', $user->id)->get();

        $retData = array();
        foreach ($allMsg as $item) {
            $text = AppointmentMsgTransformer::transformerMsgList($item);

            if ($text) {
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

        $allMsg = AppointmentMsg::where('locums_id', $user->id)->where('locums_read', 0)->get();

        $retData = array();
        foreach ($allMsg as $item) {
            $text = AppointmentMsgTransformer::transformerMsgList($item);

            if ($text) {
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
        $msg->locums_read = 1;
        $msg->save();

        return response()->json(['success' => ''], 204);
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

        AppointmentMsg::where('locums_id', $user->id)->update(['locums_read' => 1]);

        return response()->json(['success' => ''], 204);
    }
}
