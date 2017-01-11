<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Requests\BankRequest;
use App\Api\Requests\BankUpdateRequest;
use App\Api\Transformers\BankTransformer;
use App\DoctorBank;
use Illuminate\Http\Request;
use App\User;

class BankController extends BaseController
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

        $bankInfo = DoctorBank::where('doctor_id', $user->id)->get();
        $data = array();
        foreach ($bankInfo as $item) {
            array_push($data, BankTransformer::transform($item));
        }
        return response()->json(compact('data'));
    }

    /**
     * @param BankRequest $request
     * @return \Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse|mixed
     */
    public function store(BankRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $data = [
            'doctor_id' => $user->id,
            'bank_name' => $request['name'],
            'bank_info' => $request['info'],
            'bank_no' => $request['no'],
            'desc' => $request['desc']
        ];

        try {
            DoctorBank::create($data);

            $bankInfo = DoctorBank::where('doctor_id', $user->id)->get();
            $data = array();
            foreach ($bankInfo as $item) {
                array_push($data, BankTransformer::transform($item));
            }
            return response()->json(compact('data'));
        } catch (\Exception $e) {
            return response()->json(['message' => '入库失败'], 500);
        }
    }

    /**
     * @param BankUpdateRequest $request
     * @return \Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse|mixed
     */
    public function update(BankUpdateRequest $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $bank = DoctorBank::find($request['id']);
        $bank->bank_name = $request['name'];
        $bank->bank_info = $request['info'];
        $bank->bank_no = $request['no'];
        $bank->desc = $request['desc'];

        try {
            $bank->save();

            $bankInfo = DoctorBank::where('doctor_id', $user->id)->get();
            $data = array();
            foreach ($bankInfo as $item) {
                array_push($data, BankTransformer::transform($item));
            }
            return response()->json(compact('data'));
        } catch (\Exception $e) {
            return response()->json(['message' => '入库失败'], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse|mixed
     */
    public function destroy(Request $request)
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        try {
            $bank = DoctorBank::find($request['id']);
            if ($bank != null) {
                DoctorBank::where('id', $request['id'])->delete();
            }

            $bankInfo = DoctorBank::where('doctor_id', $user->id)->get();
            $data = array();
            foreach ($bankInfo as $item) {
                array_push($data, BankTransformer::transform($item));
            }
            return response()->json(compact('data'));
        } catch (\Exception $e) {
            return response()->json(['message' => '查询/删除失败'], 500);
        }
    }
}
