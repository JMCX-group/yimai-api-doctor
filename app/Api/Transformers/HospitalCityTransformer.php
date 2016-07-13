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

class HospitalCityTransformer extends TransformerAbstract
{
    /**
     * @param Hospital $hospital
     * @return array
     */
    public function transform(Hospital $hospital)
    {
        return [
            'id' => $hospital['id'],
            'name' => $hospital['name']
        ];
    }
}
