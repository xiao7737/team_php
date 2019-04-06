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
use think\Db;
use think\facade\Request;

/**
 * todo 球队管理员操作：注册，登录，球队管理，球员管理
 * Class Admin
 * @package app\admin\controller
 */
class Admin extends Controller
{
    /**
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
        $account     = Request::param('account');
        $pw          = Request::param('pw');
        $question    = Request::param('question', '');
        $question_pw = Request::param('question_pw', '');

        $data = AdminModel::where('account', $account)->find();
        if ($data) {
            return json(['msg' => '该账号已经注册,请直接登录']);
        }
        //插入数据库
        $adminID = Db('admin')
            ->insert([
                'account'     => $account,
                'pw'          => $pw,
                'question'    => $question,
                'question_pw' => $question_pw,
            ]);
        if ($adminID) {
            return json(['msg' => '注册成功', 'status' => 1]);
        } else {
            return json(['msg' => '注册失败', 'status' => 2]);
        }
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login()        //也可以使用在方法中使用依赖注入
    {
        $account = Request::param('account');
        $pw      = Request::param('pw');
        $res     = AdminModel::where('account', $account)
            ->where('pw', $pw)
            ->field('id')
            ->find();
        if ($res) {
            return json(['msg' => '登录成功', 'status' => 1, 'adminID' => $res['id']]);
        }
        return json(['msg' => '密码或账号错误', 'status' => 2, 'adminID' => '']);
    }


    /**
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function updatePw()
    {
        $account = Request::param('account', 'admin1');
        $old_pw  = Request::param('pw', 'admin1');
        $new_pw  = Request::param('pw', 'admin1');
        if ($old_pw == $new_pw)
            return json(['msg' => '新密码和旧密码一致', 'status' => 4]);

        $res = AdminModel::where('account', $account)
            ->where('pw', $old_pw)
            ->field('id')
            ->find();
        if ($res) {
            $updateRes = AdminModel::where('id', $res['id'])
                ->update(['pw' => $new_pw]);
            if ($updateRes) {
                return json(['msg' => '重置密码成功', 'status' => 1]);
            } else {
                return json(['msg' => 'server error', 'status' => 2]);
            }
        }
        return json(['msg' => '密码或账号错误', 'status' => 3]);
    }
}