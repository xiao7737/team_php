<?php
/**
 * User: xiaoxichuan
 * Date: 2019/4/3
 * Time: 10:50
 * 　　   ┏┓　 ┏┓
 * 　┏━━━━┛┻━━━┛┻━━━┓
 * 　┃              ┃
 * 　┃       ━　    ┃
 * 　┃　  ┳┛ 　┗┳   ┃
 * 　┃              ┃
 * 　┃       ┻　    ┃
 * 　┗━━━┓      ┏━━━┛
 */

namespace app\admin\controller;

use think\Controller;
use app\admin\model\Admin as AdminModel;
use think\facade\Request;

/**
 * todo 球队管理员操作：注册，登录，球队管理，球员管理
 * Class Admin
 * @package app\admin\controller
 */
class Admin extends Controller
{
    /**
     * todo 管理员注册:接收手机号和密码，密保问题和答案，检测该用户是否已经注册
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function register()
    {
        //return $this->request->param('name1');  //获取参数
        //return request()->param('name');      //也可以直接调用request()函数
        //return Request::param('name');          //也可以使用静态调用的方式，但是注意use的是facade
        //return Request::url(true);
        /* return json(['admin_id' => 1,
                      'status '  => 1,
                      'username' => '肖西川'
         ]);*/
        $data = AdminModel::where('id', 1)->select();
        return $data;
    }

    public function login(Request $request)        //也可以使用在方法中使用依赖注入
    {
        return json($request->param('name'));
    }
}