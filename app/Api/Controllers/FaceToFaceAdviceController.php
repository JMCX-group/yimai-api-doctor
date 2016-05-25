<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\FaceToFaceAdvice;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class FaceToFaceAdviceController extends BaseController
{
    public function index()
    {
    }

    /**
     * 新建一个面诊
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse|mixed
     */
    public function store(Request $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $data = [
            'doctor_id' => $user->id,
            'phone' => $request['phone'],
            'name' => $request['name'],
            'price' => $user->fee_face_to_face,
            'transaction_id' => '', //TODO 接入微信支付后需要生成订单号和二维码
            'out_trade_no' => '',
            'status' => 'wait_pay'
        ];

        try {
            $faceToFaceAdvice = FaceToFaceAdvice::create($data);
        } catch (JWTException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        }

        $QrCode = ''; //TODO 接入微信支付后需要生成订单号和二维码

        return [
            'data' => [
                'id' => $faceToFaceAdvice->id,
                'price' => $faceToFaceAdvice->price, //TODO 需要增加平台手续费
                'qr_code' => ''
            ]
        ];
    }
}
