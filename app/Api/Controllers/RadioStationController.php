<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:47
 */

namespace App\Api\Controllers;

use App\Api\Transformers\RadioStationTransformer;
use App\DoctorRadioRead;
use App\RadioStation;
use App\User;
use Illuminate\Http\Request;

class RadioStationController extends BaseController
{
    /**
     * @return \Dingo\Api\Http\Response|mixed
     */
    public function index()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $radioStations = RadioStation::getRadioList($user);

        return $this->response->paginator($radioStations, new RadioStationTransformer());
    }

    /**
     * 已读后删除对应消息
     * 
     * @param Request $request
     * @return \Dingo\Api\Http\Response|mixed
     */
    public function readStatus(Request $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        DoctorRadioRead::where('user_id', $user->id)
            ->where('radio_station_id', $request->id)
            ->delete();

        return response()->json(['success' => ''], 204);
    }

    /**
     * 全部消息已读
     *
     * @return \Dingo\Api\Http\Response|mixed
     */
    public function allRead()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        DoctorRadioRead::where('user_id', $user->id)->delete();

        return response()->json(['success' => ''], 204);
    }
}
