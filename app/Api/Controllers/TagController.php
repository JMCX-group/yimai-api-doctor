<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Transformers\TagTransformer;
use App\Tag;

class TagController extends BaseController
{
    /**
     * Get all.
     * 
     * @return mixed
     */
    public function index()
    {
        $tags = Tag::all();

        return $this->response->collection($tags, new TagTransformer());
    }
}
