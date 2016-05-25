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
                    '2xx' => '成功; 200表示成功获取正确的数据, 204表示执行/通讯成功,但是无返回数据',
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

                '初始化信息' => [
                    '启动软件初始化' => [
                        'url' => $http . '/api/init',
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
                                'is_auth' => '是否认证,1为认证,0为未认证',
                                'inviter' => '邀请者'
                            ],
                            'relations' => [
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
                                ]
                            ],
                            'recent_contacts' => [
                                'id' => '用户id',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'department' => '用户所在科室名称',
                                'is_auth' => '是否认证,1为认证,0为未认证'
                            ],
                            'sys_info' => [
                                'radio_unread_count' => '未读的广播数量',
                                'admissions_unread_count' => '未读的接诊信息数量',
                                'appointment_unread_count' => '未读的约诊信息数量'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

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
                                'is_auth' => '是否认证,1为认证,0为未认证',
                                'fee_switch' => '1:开, 0:关',
                                'fee' => '接诊收费金额',
                                'fee_face_to_face' => '当面咨询收费金额',
                                'inviter' => '邀请者'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '查询其他医生的信息' => [
                        'url' => $http . '/api/user/{doctor_id}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => '请前台判断是否在查询自己,自己的信息在登陆时已经有全部的了,而且看自己的没有中间的两个按钮',
                        'response' => [
                            'user' => [
                                'is_friend' => '决定按钮的布局; true | false',
                                'id' => '用户id',
                                'code' => '医脉码',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '用户职称',
                                'province' => '用户所在省份名称',
                                'city' => '用户所在城市名称',
                                'hospital' => '用户所在医院名称',
                                'department' => '用户所在科室名称',
                                'college' => '用户所在院校名称',
                                'tags' => '特长/标签',
                                'personal_introduction' => '个人简介',
                                'is_auth' => '是否认证,1为认证,0为未认证',
                                'common_friend_list' => [
                                    'id' => '用户id',
                                    'head_url' => '头像URL',
                                    'is_auth' => '是否认证,1为认证,0为未认证'
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '搜索医生信息' => [
                        'url' => $http . '/api/user/search',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'field' => '搜索的关键字; 必填项,当type为指定内容时为可选项,不过此时将会是全局搜索,返回信息量巨大',
                            'city_id' => '下拉框选择的城市ID; 可选项',
                            'hospital_id' => '下拉框选择的医院ID; 可选项',
                            'dept_id' => '下拉框选择的科室ID; 可选项',
                            'format' => '或者什么样的格式; 可选项; 提交该项,且值为android时,hospitals会返回安卓格式',
                            'type' => '普通搜索,可以不填该项或内容置空; 同医院:same_hospital; 同领域:same_department; 同院校:same_college; 可选项'
                        ],
                        '说明' => '会一次传递所有排好序的数据,一次显示5个即可; 如果下拉框为后置条件,建议前端执行过滤; 城市按省份ID分组; 医院按省份ID和城市ID级联分组',
                        'response' =>
                            [
                                'provinces' => [
                                    'id' => '省份ID, province_id',
                                    'name' => '省份/直辖市名称'
                                ],
                                'citys' => [
                                    '{province_id}' => [
                                        'id' => '城市ID, city_id',
                                        'name' => '城市名称'
                                    ]
                                ],
                                'hospitals' => [
                                    '默认格式说明' => '例如: hospitals[1][1]可以取到1省1市下的医院列表',
                                    '{province_id}' => [
                                        '{city_id}' => [
                                            '{自增的数据序号}' => [
                                                'id' => '医院ID',
                                                'name' => '城市名称',
                                                'province_id' => '该医院的省id',
                                                'city_id' => '该医院的市id'
                                            ]
                                        ]
                                    ],

                                    '安卓格式说明' => '提交format字段,且值为android时,hospitals会返回该格式 :',
                                    '{自增的数据序号}' => [
                                        'province_id' => '省份ID',
                                        'data' => [
                                            '{自增的数据序号}' => [
                                                'city_id' => '城市ID',
                                                'data' => [
                                                    '{自增的数据序号}' => [
                                                        'id' => '医院ID',
                                                        'name' => '城市名称',
                                                        'province_id' => '该医院的省id',
                                                        'city_id' => '该医院的市id'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                ],
                                'departments' => [
                                    'id' => '科室ID',
                                    'name' => '科室名称'
                                ],
                                'count' => '满足条件的医生数量',
                                'users' => [
                                    'id' => '用户ID',
                                    'name' => '用户姓名',
                                    'head_url' => '头像URL',
                                    'job_title' => '职称',
                                    'city' => '所属城市',
                                    'hospital' => [
                                        'id' => '用户所在医院ID',
                                        'name' => '用户所在医院名称'
                                    ],
                                    'department' => [
                                        'id' => '用户所在科室ID',
                                        'name' => '用户所在科室名称'
                                    ],
                                    'relation' => '1:一度人脉; 2:二度人脉; null:没关系'
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
                            'head_img' => '用户头像; 直接POST文件,支持后缀:jpg/jpeg/png',
                            'sex' => '性别',
                            'province' => '用户所在省份ID',
                            'city' => '用户所属城市ID',
                            'hospital' => '用户所属医院ID; 如果该处提交的不是医院ID，则会自动创建该医院后并返回',
                            'department' => '用户所属部门ID',
                            'job_title' => '用户职称',
                            'college' => '用户所属院校ID',
                            'ID_number' => '身份证号',
                            'tags' => '特长/标签',
                            'personal_introduction' => '个人简介',
                            'fee_switch' => '接诊收费开关, 1:开, 0:关(默认值)',
                            'fee' => '接诊收费金额,默认300',
                            'fee_face_to_face' => '当面咨询收费金额,默认100'
                        ],
                        '说明' => '以上form-data项均为可选项,修改任意一个或几个都可以,有什么数据加什么字段',
                        'response' => [
                            'user' => [
                                'id' => '用户id',
                                'code' => '医脉码',
                                'phone' => '用户注册手机号',
                                'name' => '用户姓名',
                                'head_url' => '头像URL; 相对地址,需要拼服务器域名或ip,例如:回传/uploads/a.jpg,要拼成:http://yimai.com/uploads/a.jpg; 注意url中没有api',
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
                                'is_auth' => '是否认证,1为认证,0为未认证',
                                'fee_switch' => '1:开, 0:关',
                                'fee' => '接诊收费金额',
                                'fee_face_to_face' => '当面咨询收费金额',
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
                        '说明' => '{search_field}字段传中文可能需要转码',
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
                    '新增朋友/申请好友' => [
                        'url' => $http . '/api/user/relation/add-friend',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '用户ID； 三选一即可',
                            'phone' => '用户手机号； 三选一即可',
                            'code' => '用户医脉码； 三选一即可'
                        ],
                        'response' => [
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '同意/确定申请' => [
                        'url' => $http . '/api/user/relation/confirm',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id' => '用户ID'
                        ],
                        'response' => [
                            'message' => '',
                            'error' => ''
                        ]
                    ],
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
                    ],

                    '同步最近联系人记录到服务器' => [
                        'url' => $http . '/api/user/relation/push-recent-contacts',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'id_list' => '最近联系人ID list; 例如: 1,2,3,4,5 ; 最长12个人'
                        ],
                        'response' => [
                            'message' => '',
                            'error' => ''
                        ]
                    ],
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
                        'params' => [
                            'token' => ''
                        ],
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

                '约诊' => [
                    '新建约诊' => [
                        'url' => $http . '/api/appointment/new',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'name' => '患者姓名',
                            'phone' => '患者手机号',
                            'sex' => '患者性别,1男0女',
                            'age' => '患者年龄',
                            'history' => '患者现病史',
                            'doctor' => '预约的医生的ID',
                            'time' => '预约时间,选了时间就把日期按标准时间日期格式传来,没有选时间就把1',
                            'am_or_pm' => '预约时间,选了时间就把上午或下午传来,上午:am,下午:pm',
                        ],
                        'response' => [
                            'id' => '预约码',
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '上传图片' => [
                        'url' => $http . '/api/appointment/upload-img',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'img' => '病历照片,一张张传; 直接POST文件,支持后缀:jpg/jpeg/png'
                        ],
                        'response' => [
                            'url' => '压缩后的图片访问url链接,可直接用于阅览',
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                    '获取约诊详细信息' => [
                        'url' => $http . '/api/appointment/detail/{appointment_id}',
                        'method' => 'GET',
                        'params' => [
                            'token' => ''
                        ],
                        '说明' => '还未做完,可以测试:/api/appointment/detail/011605130001?token=',
                        'response' => [
                            'doctor_info' => [
                                'id' => '用户ID',
                                'name' => '用户姓名',
                                'head_url' => '头像URL',
                                'job_title' => '职称',
                                'hospital' => '所属医院',
                                'department' => '所属科室'
                            ],
                            'patient_info' => [
                                'name' => '患者姓名',
                                'head_url' => '患者头像URL',
                                'sex' => '患者性别',
                                'age' => '患者年龄',
                                'phone' => '所属科室',
                                'history' => '病情描述',
                                'img_url' => '病历图片url序列,url中把{_thumb}替换掉就是未压缩图片,例如:/uploads/case-history/2016/05/011605130001/1463539005_thumb.jpg,原图就是:/uploads/case-history/2016/05/011605130001/1463539005.jpg',
                            ],
                            'detail_info' => [
                                'progress' => '进度,该项还未做完',
                                'time_line' => [
                                    '说明' => 'time_line数组及其内部other数组下可能有1条或多条信息,需要遍历,0和1的序号不用在意,foreach就好',
                                    0 => [
                                        'time' => '时间轴左侧的时间',
                                        'info' => [
                                            'text' => '文案描述',
                                            'other' => [
                                                0 => [
                                                    'name' => '其他的信息名称,例如:期望就诊时间',
                                                    'content' => '其他的信息内容,例如:2016-05-18 上午'
                                                ]
                                            ]
                                        ],
                                        'type' => '决定使用什么icon; begin | wait'
                                    ],
                                    1 => [
                                        'time' => '时间轴左侧的时间, null为没有',
                                        'info' => [
                                            'text' => '文案描述',
                                            'other' => 'null为没有'
                                        ],
                                        'type' => '决定使用什么icon; begin | wait'
                                    ]
                                ]
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ]
                ],

                '当面咨询' => [
                    '新建当面咨询' => [
                        'url' => $http . '/api/f2f-advice/new',
                        'method' => 'POST',
                        'params' => [
                            'token' => ''
                        ],
                        'form-data' => [
                            'phone' => '患者手机号',
                            'name' => '患者姓名'
                        ],
                        'response' => [
                            'data' => [
                                'id' => '当面咨询ID',
                                'price' => '总共支付的价格,含医生收入和平台收入',
                                'qr_code' => '提供扫描支付的二维码url'
                            ],
                            'message' => '',
                            'error' => ''
                        ]
                    ],
                ],

                '患者信息' => [
                    '所有广播' => [
                        'url' => $http . '/api/patient/get-by-phone',
                        'method' => 'GET',
                        'params' => [
                            'token' => '',
                            'phone' => '患者手机号'
                        ],
                        '说明' => '没有注册,则返回信息为[]',
                        'response' => [
                            'data' => [
                                'id' => '患者ID',
                                'phone' => '患者手机号',
                                'name' => '患者姓名',
                                'sex' => '患者性别',
                                'age' => '患者年龄'
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
