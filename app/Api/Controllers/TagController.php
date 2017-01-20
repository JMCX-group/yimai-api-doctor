<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/21
 * Time: 上午9:45
 */

namespace App\Api\Controllers;

use App\Api\Transformers\TagTransformer;
use App\DeptStandard;
use App\Tag;
use App\User;

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

    /**
     * 根据请求者获取分组的tag信息
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function group()
    {
        $user = User::getAuthenticatedUser();
        if (!isset($user->id)) {
            return $user;
        }

        $data = array();
        $dept = DeptStandard::find($user->dept_id);

        if ($dept->parent_id == 0) {
            /**
             * 一级科室流程，获取全部二级科室并分组：
             */
            $tags = Tag::getDeptLv2Tags($user->dept_id);
            $deptLv2List = DeptStandard::select('id', 'name')->where('parent_id', $user->dept_id)->get();

            foreach ($deptLv2List as $item) {
                $tmpData = array();
                foreach ($tags as $tag) {
                    if ($tag->dept_lv2_id == $item->id) {
                        array_push($tmpData, TagTransformer::deptLv2Data($tag));
                    }
                }
                $newData = [
                    'dept' => $item->name,
                    'tags' => $tmpData
                ];
                array_push($data, $newData);
            }


        } else {
            /**
             * 获取相应二级科室的：
             */
            $tags = Tag::select('id', 'name')->where('dept_lv2_id', $user->dept_id)->get();
            $newData = [
                'dept' => DeptStandard::find($user->dept_id)->name,
                'tags' => $tags
            ];
            array_push($data, $newData);
        }

        return response()->json(compact('data'));
    }
}
