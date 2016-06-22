<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Transformers\CollegeTransformer;
use App\College;

class CollegeController extends BaseController
{
    /**
     * Get all.
     * 
     * @return mixed
     */
    public function index()
    {
        $colleges = College::all();

        return $this->response->collection($colleges, new CollegeTransformer());
    }
}
