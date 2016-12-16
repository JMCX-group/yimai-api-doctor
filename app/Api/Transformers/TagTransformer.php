<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午4:08
 */

namespace App\Api\Transformers;

use App\Tag;
use League\Fractal\TransformerAbstract;

class TagTransformer extends TransformerAbstract
{
    public function transform(Tag $tag)
    {
        return [
            'id' => $tag['id'],
            'name' => $tag['name']
        ];
    }

    /**
     * Dept lv2.
     *
     * @param $tagData
     * @return array
     */
    public static function deptLv2Data($tagData)
    {
        return [
            'id' => $tagData->id,
            'name' => $tagData->name,
            'dept' => $tagData->dept_name,
            'hot' => $tagData->hot
        ];
    }
}
