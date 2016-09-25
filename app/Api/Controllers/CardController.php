<?php
/**
 * Created by PhpStorm.
 * User: 乔小柒
 * Date: 2016/9/25
 * Time: 15:15
 */
namespace App\Api\Controllers;

use App\User;

class CardController extends BaseController
{
    /**
     * 申请名片。
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function submit()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $user->application_card = 1;
        $user->save();

        return response()->json(['success' => ''], 204);
    }
}
