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

/**
 * todo 球队管理员操作：注册，登录，球队管理，球员管理
 * Class Admin
 * @package app\admin\controller
 */
class Admin extends Controller
{
    public function register()  //todo phone+pw
    {
        return $this->request->param('name1');  //获取参数
       /* return json(['admin_id' => 1,
                     'status '  => 1,
                     'username' => '肖西川'
        ]);*/                                   //返回结果格式
    }

    public function login()
    {
        return json("login test");
    }
}