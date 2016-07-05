<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\City;
use League\Fractal\TransformerAbstract;

class CityTransformer extends TransformerAbstract
{
    /**
     * @param City $city
     * @return array
     */
    public function transform(City $city)
    {
        return [];
    }
}
