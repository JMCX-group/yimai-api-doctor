<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:47
 */

namespace App\Api\Controllers;

use App\Api\Transformers\DeptTransformer;
use App\DeptStandard;

/**
 * Class DeptStandardController
 * @package App\Api\Controllers
 */
class DeptStandardController extends BaseController
{
    /**
     * All dept.
     *
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $dept = DeptStandard::all();
        
        return $this->response->collection($dept, new DeptTransformer());
    }
}
