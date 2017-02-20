<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/20
 * Time: 下午2:27
 */

namespace App\Api\Requests;

use App\Http\Requests\Request;

class AppointmentRequest extends Request
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
            'name' => 'between:1,10',
            'phone' => 'required|digits_between:11,11',
            'doctor' => 'required|exists:doctors,id',
            'date' => 'required'
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'required' => ':attribute不能为空',
            'between' => ':attribute长度必须在:min和:max之间',
            'digits_between' => ':attribute必须为:min位长的数字',
            'exists' => ':attribute不存在'
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => '姓名',
            'phone' => '手机号',
            'doctor' => '医生',
            'date' => '期望就诊时间',
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
