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
use think\facade\Request;

/**
 * todo 球队管理员操作：注册，登录，球队管理，球员管理
 * Class Admin
 * @package app\admin\controller
 */
class Admin extends Controller
{
    public function register()  //todo phone+pw        如果已经继承基类，则已经自动完成了构造方法注入
    {
        //return $this->request->param('name1');  //获取参数
        //return request()->param('name');      //也可以直接调用request()函数
        //return Request::param('name');          //也可以使用静态调用的方式，但是注意use的是facade
        return Request::url(true);
        /* return json(['admin_id' => 1,
                      'status '  => 1,
                      'username' => '肖西川'
         ]);*/
    }

    public function login(Request $request)        //也可以使用在方法中使用依赖注入
    {
        return json($request->param('name'));
    }
}