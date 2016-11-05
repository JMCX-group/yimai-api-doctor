<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Transformers\WalletTransformer;
use App\DoctorWallet;
use App\User;

class WalletController extends BaseController
{
    public function info()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $walletInfo = DoctorWallet::where('doctor_id', $user->id)->first();
        if (!isset($walletInfo->doctor_id)) {
            $walletInfo = DoctorWallet::insert(['doctor_id' => $user->id]);
        }

        return $this->response->item($walletInfo, new WalletTransformer());
    }
}
