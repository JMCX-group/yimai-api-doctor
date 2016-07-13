<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/20
 * Time: 下午2:27
 */

namespace App\Api\Requests;

use App\Http\Requests\Request;

class RefusalAdmissionsRequest extends Request
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
        $rules = [
            'id' => 'required',
            'reason' => 'required'
        ];

        return $rules;

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
            'id' => '接诊号',
            'reason' => '拒绝理由'
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
