<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\Hospital;
use League\Fractal\TransformerAbstract;

/**
 * Class HospitalTransformer
 * @package App\Api\Transformers
 */
class HospitalTransformer extends TransformerAbstract
{
    /**
     * @param Hospital $hospital
     * @return array
     */
    public function transform(Hospital $hospital)
    {
        return [
            'id' => $hospital['id'],
            'area' => $hospital['area'],
            'province' => $hospital['province'],
            'city' => $hospital['city'],
            'name' => $hospital['name'],
            '3a' => $hospital['three_a'],
            'top' => $hospital['top_dept_num']
        ];
    }
}
