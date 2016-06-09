<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/20
 * Time: 下午2:27
 */

namespace App\Api\Requests;

use App\Http\Requests\Request;

class AgreeAdmissionsRequest extends Request
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
            'reason' => 'required_if:status,no',
            'visit_time' => 'required_unless:status,no'
        ];

        return $rules;

    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'required' => ':attribute不能为空',
            'required_if' => '如果拒绝,必须选择或输入:attribute',
            'required_unless' => '如果同意,必须选择或输入:attribute'
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'id' => '接诊号',
            'reason' => '拒绝理由',
            'visit_time' => '约诊时间'
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
