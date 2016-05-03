<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/20
 * Time: 下午2:27
 */

namespace App\Api\Requests;

use App\Http\Requests\Request;

/**
 * Class AuthRequest
 * @package App\Api\Requests
 */
class ResetPwdRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'phone' => 'required|digits_between:11,11',
            'password' => 'required|between:6,60',
            'verify_code' => 'required|digits_between:4,4|exists:user_verify_codes,code,phone,' . $_POST['phone']
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'required' => ':attribute不能为空',
            'digits_between' => ':attribute必须为:min位长的数字',
            'unique' => ':attribute已注册',
            'between' => ':attribute长度必须在:min和:max之间',
            'exists' => ':attribute错误'
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'phone' => '手机号码',
            'password' => '用户密码',
            'verify_code' => '验证码'
        ];
    }

    /**
     * @param array $errors
     * @return mixed
     */
    public function response(array $errors)
    {
        return response()->json(['message' => current($errors)[0]], 403);
    }
}
