<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\College;
use League\Fractal\TransformerAbstract;

class CollegeTransformer extends TransformerAbstract
{
    public function transform(College $college)
    {
        return [
            'id' => $college['id'],
            'name' => $college['name']
        ];
    }
}
