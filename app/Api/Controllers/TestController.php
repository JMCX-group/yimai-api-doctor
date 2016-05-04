<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Transformers\UserTransformer;
use App\AppDoctorRelation;
use App\City;
use App\Province;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use JWTAuth;

class TestController extends BaseController
{
    public function index()
    {
        return AppDoctorRelation::getNewFriends(1);
    }

    public function test()
    {

    }
}
