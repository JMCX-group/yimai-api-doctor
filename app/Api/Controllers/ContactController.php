<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Transformers\ContactTransformer;
use App\Contact;
use App\User;

class ContactController extends BaseController
{
    /**
     * 获取登陆用户全部数据
     * 
     * @return \Dingo\Api\Http\Response|mixed
     */
    public function index()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $contacts = Contact::where('doctor_id', $user->id)->get();

        return $this->response->collection($contacts, new ContactTransformer());
    }

    public function store()
    {
        
    }
}
