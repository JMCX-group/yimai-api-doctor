<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/20
 * Time: 下午2:27
 */

namespace App\Api\Requests;

use App\Http\Requests\Request;

class SearchUserRequest extends Request
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
            'field' => 'required'
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'required' => ':attribute不能为空'
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'field' => '搜索字段'
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
