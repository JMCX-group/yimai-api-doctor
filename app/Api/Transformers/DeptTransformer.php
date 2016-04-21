<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\DeptStandard;
use League\Fractal\TransformerAbstract;

/**
 * Class HospitalTransformer
 * @package App\Api\Transformers
 */
class DeptTransformer extends TransformerAbstract
{
    /**
     * @param DeptStandard $deptStandard
     * @return array
     */
    public function transform(DeptStandard $deptStandard)
    {
        return [
            'id' => $deptStandard['id'],
            'name' => $deptStandard['name']
        ];
    }
}
