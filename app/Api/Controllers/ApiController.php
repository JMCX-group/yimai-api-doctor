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
                '特别说明' => '由于框架原因,Token相关和业务相关的错误返回字段无法保持自定义统一',
                'HTTP状态码速记' => [
                    '释义' => 'HTTP状态码有五个不同的类别:',
                    '1xx' => '临时/信息响应',
                    '2xx' => '成功',
                    '3xx' => '重定向',
                    '4xx' => '客户端/请求错误; 需检查url拼接和参数; 在我们这会出现可以提示的[message]或需要重新登录获取token的[error]',
                    '5xx' => '服务器错误; 可以提示服务器崩溃/很忙啦~',
                ]
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
                                'head_url' => '头像URL',
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
                    '搜索医生信息' => [
                        'url' => $http . '/api/user/search',
                        'method' => 'POST',
                        'params' => [
                            'token' => '',
                            'field' => '搜索的关键字; 必填项',
                            'city_id' => '下拉框选择的城市ID; 可选项',
                            'hospital_id' => '下拉框选择的医院ID; 可选项',
                            'dept_id' => '下拉框选择的科室ID; 可选项'
                        ],
                        '说明' => '会一次传递所有排好序的数据,一次显示5个即可; 如果下拉框为后置条件,建议前端执行过滤',
                        'response' => [
                            'count' => '满足条件的医生数量',
                            'users' => [
                                'id' => '用户ID',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '职称',
                                'city' => '所属城市',
                                'hospital' => '所属医院',
                                'department' => '所属科室'
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
                                'head_url' => '头像URL',
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
                                'id' => '医院ID',
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
                        '说明' => '已按三甲医院顺序排序',
                        'response' => [
                            'data' => [
                                'id' => '医院ID',
                                'name' => '医院名称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '模糊查询某个医院名称' => [
                        'url' => $http . '/api/hospital/search/{search_field}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'data' => [
                                'id' => '医院ID',
                                'name' => '医院名称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '科室信息' => [
                    '所有科室' => [
                        'url' => $http . '/api/dept',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        'response' => [
                            'data' => [
                                'id' => '科室ID',
                                'name' => '科室名称'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '医脉资源' => [
                    '一度医脉(四部分数据,多用于首次/当天首次打开)' => [
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
                            'unread' => '好友信息的未读数量',
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
                    '一度医脉(两部分数据,多用于打开后第一次之后的刷新数据用)' => [
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
                        '说明' => 'friends中的数据块已按common_friend_count的倒序排序',
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
                                'job_title' => '职称',
                                'common_friend_count' => '共同好友数量'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '新朋友' => [
                        'url' => $http . '/api/relation/new-friends',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => 'friends中的数据块已按添加好友的时间倒序排序; 获取之后,该次所有数据的未读状态将自动置为已读',
                        'response' => [
                            'friends' => [
                                'id' => '用户ID',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'hospital' => '所属医院',
                                'department' => '所属科室',
                                'unread' => '未读状态,1为已读,0为未读',
                                'status' => '与好友的状态; isFriend | waitForSure | waitForFriendAgree',
                                'word' => '显示文案'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '广播信息' => [
                    '所有广播' => [
                        'url' => $http . '/api/radio',
                        'method' => 'GET',
                        'params' => [
                            'token' => '',
                            'page' => '页码,一页4个; 没有填页码默认是第一页'
                        ],
                        'response' => [
                            'data' => [
                                'id' => '广播ID',
                                'name' => '广播标题',
                                'content' => '广播内容',
                                'img_url' => '首页图片URL',
                                'author' => '发表人',
                                'time' => '发表时间',
                                'unread' => '是否未读,1为未读,null为已读'
                            ],
                            'meta' => [
                                'pagination' => [
                                    'total' => '广播总共的数量',
                                    'count' => '该次请求获取的数量',
                                    'per_page' => '每页将请求数据量',
                                    'current_page' => '当前页码(page)',
                                    'total_pages' => '总共页码(page)',
                                    'links' => [
                                        'next' => '会自动生成下一页链接,类似于:http://localhost/api/radio?page=2'
                                    ]
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '广播已读' => [
                        'url' => $http . '/api/radio/read',
                        'method' => 'POST',
                        'form-data' => [
                            'id' => '广播ID'
                        ],
                        'response' => [
                            'Status Code' => '204',
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

            ]
        ];

        return $api;
    }

    public function item()
    {
        $http = env('MY_API_HTTP_HEAD', 'http://localhost');

        $api = [
            'API文档目录' => [
                'API文档' => [
                    'url' => $http . '/api',
                    'method' => 'GET'
                ],
                '用户' => [
                    '地址' => [],
                    '包含' => '注册/发送验证码/获取邀请人/登录/重置密码'
                ]
            ],
            '需要Token验证' => [
                '用户' => [
                    '地址' => [],
                    '包含' => '注册/发送验证码/获取邀请人/登录/重置密码'
                ],
                '用户信息' => [
                    '查询个人信息' => [],
                    '修改个人信息' => []
                ],
                '省市信息' => [
                    '省市列表' => [],
                    '省市列表-按省分组' => []
                ],
                '医院信息' => [
                    '单个医院' => [],
                    '属于某个城市下的医院' => [],
                    '模糊查询某个医院名称' => []
                ],
                '科室信息' => [
                    '所有科室' => []
                ],
                '医脉资源' => [
                    '一度医脉(四部分数据,多用于首次/当天首次打开)' => [],
                    '一度医脉(两部分数据,多用于打开后第一次之后的刷新数据用)' => [],
                    '二度医脉(两部分数据)' => [],
                    '新朋友' => []
                ],
                '广播信息' => [
                    '所有广播' => [],
                    '广播已读' => []
                ]

            ]
        ];

        return $api;
    }
}
