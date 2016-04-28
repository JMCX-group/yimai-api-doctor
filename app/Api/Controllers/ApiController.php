<?php
/**
 * Created by PhpStorm.
 * User: lyx
 * Date: 16/4/18
 * Time: 下午3:09
 */

namespace App\Api\Controllers;

/**
 * Class HospitalsController
 * @package App\Api\Controllers
 */
class ApiController extends BaseController
{
    /**
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $http = env('MY_API_HTTP_HEAD', 'http://localhost');

        $api = [
            '统一说明' => [
                '数据格式' => 'JSON',
                'url字段' => 'HTTP请求地址; {}表示在链接后直接跟该数据的ID值即可,例:http://api/hospital/77?token=xx,能获取id为77的医院信息',
                'method字段' => 'GET / POST',
                'form-data字段' => '表单数据',
                'response字段' => [
                    'error字段' => 'Token验证信息错误,表示需要重新登录获取Token; 在HTTP状态码非200时,才会有该字段',
                    'message字段' => '业务信息错误; 在HTTP状态码非200时,才会有该字段',
                    'debug字段' => '只有内测时有,用于传递一些非公开数据或调试信息',
                ],
                '特别说明' => '由于框架原因,Token相关和业务相关的错误返回字段无法保持自定义统一'
            ],

            '无需Token验证' => [
                'API文档' => [
                    'url' => $http . '/api',
                    'method' => 'GET'
                ],

                '用户' => [
                    '注册' => [
                        'url' => $http . '/api/user/register',
                        'method' => 'POST',
                        'form-data' => [
                            'phone' => '11位长的纯数字手机号码',
                            'password' => '6-60位密码',
                            'verify_code' => '4位数字验证码'
                        ],
                        'response' => [
                            'token' => '成功后会返回登录之后的token值',
                            'message' => ''
                        ]
                    ],
                    '发送验证码' => [
                        'url' => $http . '/api/user/verify-code',
                        'method' => 'POST',
                        'form-data' => [
                            'phone' => '11位长的纯数字手机号码'
                        ],
                        'response' => [
                            'debug' => '为了测试方便,成功后会返回随机的4位手机验证码,正式版上线时没有该项',
                            'message' => ''
                        ]
                    ],
                    '获取邀请人' => [
                        'url' => $http . '/api/user/inviter',
                        'method' => 'POST',
                        'form-data' => [
                            'inviter' => '8位长的纯数字号码'
                        ],
                        'response' => [
                            'name' => '返回正确数字号码对应的用户姓名',
                            'message' => ''
                        ]
                    ],
                    '登录' => [
                        'url' => $http . '/api/user/login',
                        'method' => 'POST',
                        'form-data' => [
                            'phone' => '11位长的纯数字手机号码',
                            'password' => '6-60位密码'
                        ],
                        'response' => [
                            'token' => '成功后会返回登录之后的token值',
                            'message' => ''
                        ]
                    ],
                    '重置密码' => [
                        'url' => $http . '/api/user/reset-pwd',
                        'method' => 'POST',
                        'form-data' => [
                            'phone' => '11位长的纯数字手机号码',
                            'password' => '6-60位密码',
                            'verify_code' => '4位数字验证码'
                        ],
                        'response' => [
                            'token' => '成功后会返回登录之后的token值',
                            'message' => ''
                        ]
                    ]
                ]
            ],

            '需要Token验证' => [
                '用户信息' => [
                    '查询个人信息' => [
                        'url' => $http . '/api/user/me',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'user' => [
                                'id' => '用户id',
                                'code' => '医脉码',
                                'phone' => '用户注册手机号',
                                'name' => '用户姓名',
                                'sex' => '性别',
                                'province' => [
                                    'id' => '用户所在省份ID',
                                    'name' => '用户所在省份名称'
                                ],
                                'city' => [
                                    'id' => '用户所在城市ID',
                                    'name' => '用户所在城市名称'
                                ],
                                'hospital' => [
                                    'id' => '用户所在医院ID',
                                    'name' => '用户所在医院名称'
                                ],
                                'department' => [
                                    'id' => '用户所在科室ID',
                                    'name' => '用户所在科室名称'
                                ],
                                'job_title' => '用户职称',
                                'college' => [
                                    'id' => '用户所在院校ID',
                                    'name' => '用户所在院校名称'
                                ],
                                'ID_number' => '身份证',
                                'tags' => '特长/标签',
                                'personal_introduction' => '个人简介',
                                'inviter' => '邀请者'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '修改个人信息' => [
                        'url' => $http . '/api/user',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'name' => '用户姓名',
                            'sex' => '性别',
                            'province' => '用户所在省份ID',
                            'city' => '用户所属城市ID',
                            'hospital' => '用户所属医院ID; 如果该处提交的不是医院ID，则会自动创建该医院后并返回',
                            'department' => '用户所属部门ID',
                            'job_title' => '用户职称',
                            'college' => '用户所属院校ID',
                            'ID_number' => '身份证号',
                            'tags' => '特长/标签',
                            'personal_introduction' => '个人简介'
                        ],
                        'response' => [
                            'user' => [
                                'id' => '用户id',
                                'code' => '医脉码',
                                'phone' => '用户注册手机号',
                                'name' => '用户姓名',
                                'sex' => '性别',
                                'province' => [
                                    'id' => '用户所在省份ID',
                                    'name' => '用户所在省份名称'
                                ],
                                'city' => [
                                    'id' => '用户所在城市ID',
                                    'name' => '用户所在城市名称'
                                ],
                                'hospital' => [
                                    'id' => '用户所在医院ID',
                                    'name' => '用户所在医院名称'
                                ],
                                'department' => [
                                    'id' => '用户所在科室ID',
                                    'name' => '用户所在科室名称'
                                ],
                                'job_title' => '用户职称',
                                'college' => [
                                    'id' => '用户所在院校ID',
                                    'name' => '用户所在院校名称'
                                ],
                                'ID_number' => '身份证',
                                'tags' => '特长/标签',
                                'personal_introduction' => '个人简介',
                                'inviter' => '邀请者'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                ],

                '省市信息' => [
                    '省市列表' => [
                        'url' => $http . '/api/city',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'provinces' => [
                                'id' => '省份ID, province_id',
                                'name' => '省份/直辖市名称'
                            ],
                            'citys' => [
                                'id' => '城市ID',
                                'province_id' => '省份ID',
                                'name' => '城市名称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '省市列表-按省分组' => [
                        'url' => $http . '/api/city/group',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'provinces' => [
                                'id' => '省份ID, province_id',
                                'name' => '省份/直辖市名称'
                            ],
                            'citys' => [
                                '{province_id}' => [
                                    'id' => '城市ID',
                                    'name' => '城市名称'
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '医院信息' => [
                    '单个医院' => [
                        'url' => $http . '/api/hospital/{hospital_id}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'data' => [
                                'id' => '',
                                'area' => '所属地区',
                                'province' => '省份',
                                'city' => '城市',
                                'name' => '医院名称',
                                '3a' => '是否为三甲医院; 1:三甲, 0:非三甲',
                                'top' => '顶级科室的数量',
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '属于某个城市下的医院' => [
                        'url' => $http . '/api/hospital/city/{city_id}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'data' => [
                                'id' => '',
                                'name' => '医院名称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                ],

                '医脉资源' => [
                    '一度医脉(三部分数据)' => [
                        'url' => $http . '/api/relation',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'same' => [
                                'hospital' => '同医院的人数',
                                'department' => '同领域的人数',
                                'college' => '同学校的人数'
                            ],
                            'count' => [
                                'doctor' => '我的朋友中共有多少名医生',
                                'hospital' => '我的朋友中分别属于多少家医院'
                            ],
                            'friends' => [
                                'id' => '用户ID',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'hospital' => '所属医院',
                                'department' => '所属科室',
                                'job_title' => '职称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '一度医脉(两部分数据)' => [
                        'url' => $http . '/api/relation/friends',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'count' => [
                                'doctor' => '我的朋友中共有多少名医生',
                                'hospital' => '我的朋友中分别属于多少家医院'
                            ],
                            'friends' => [
                                'id' => '用户ID',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'hospital' => '所属医院',
                                'department' => '所属科室',
                                'job_title' => '职称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '二度医脉(两部分数据)' => [
                        'url' => $http . '/api/relation/friends-friends',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'count' => [
                                'doctor' => '我的朋友中共有多少名医生',
                                'hospital' => '我的朋友中分别属于多少家医院'
                            ],
                            'friends' => [
                                'id' => '用户ID',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'hospital' => '所属医院',
                                'department' => '所属科室',
                                'job_title' => '职称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ]
            ]
        ];

        return $api;
    }
}
