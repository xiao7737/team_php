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
use think\facade\Validate;

/**
 *  球队管理员操作：注册，登录，球队管理，球员管理
 * Class Admin
 * @package app\admin\controller
 */
class Admin extends Controller
{
    /**
     * @api {post} /admin_register  用户注册
     * @apiGroup  admin
     * @apiParam {Number}   account     账号（手机号）.
     * @apiParam {String}   pw  密码.
     * @apiParam {String}   question  密保问题.
     * @apiParam {String}   question_pw  密保答案.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：注册成功，2：注册失败，3：参数验证失败，4该账号已经被注册，请直接登录）
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

        $rule     = [
            'account|用户账号'     => 'require|integer',
            'pw|密码'            => 'require',
            'question|密保问题'    => 'require',
            'question_pw|密保答案' => 'require',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $account     = Request::param('account');
        $pw          = Request::param('pw');
        $question    = Request::param('question');
        $question_pw = Request::param('question_pw');

        $data = AdminModel::where('account', $account)->find();
        if ($data) {
            return json(['msg' => '该账号已经注册,请直接登录', 'status' => 4]);
        }
        //插入数据库
        $adminID = Db('admin')
            ->insert([
                'account'     => $account,
                'pw'          => md5($pw),
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
     * @api {post} /admin_login  用户登录
     * @apiGroup  admin
     * @apiParam {Number}   account     账号（手机号）.
     * @apiParam {String}   pw  密码.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：登录成功，2：密码或账号错误，3：参数验证失败）
     * * @apiSuccess {Number} is_admin （身份标识：-1普通注册，0球员，1及以上，代表创建的球队个数）.
     * @apiSuccessExample {json} Success-Response:
     *{
     * "msg": "登录成功",
     * "status": 1,
     * "data": {
     * "user_id": 4,
     * "is_admin": 2
     * }
     * }
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login()        //也可以使用在方法中使用依赖注入
    {
        $rule     = [
            'account|用户账号' => 'require',
            'pw|密码'        => 'require',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $account = Request::param('account');
        $pw      = Request::param('pw');
        $res     = AdminModel::where('account', $account)
            ->where('pw', md5($pw))
            ->field('id, is_admin')
            ->find();

        $data = [
            'user_id'  => $res['id'],
            'is_admin' => $res['is_admin']     //1 管理员，0 普通球员,-1 新注册用户，即非球员非管理员
        ];
        if ($res) {
            return json(['msg' => '登录成功', 'status' => 1, 'data' => $data]);
        }

        return json(['msg' => '密码或账号错误', 'status' => 2]);
    }


    /**
     * @api {post} /admin_update_pw  用户更新密码
     * @apiGroup  admin
     * @apiParam {Number}   account     账号（手机号）.
     * @apiParam {String}   old_pw  旧密码.
     * @apiParam {String}   new_pw  新密码.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：重置密码成功，2：失败，3：参数验证失败，4新密码和旧密码一致，5该账号不存在）
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function updatePw()
    {
        $rule     = [
            'account|用户账号' => 'require',
            'old_pw|旧密码'   => 'require',
            'new_pw|新密码'   => 'require|between:6,14',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $account = Request::param('account');
        $old_pw  = Request::param('old_pw');
        $new_pw  = Request::param('new_pw');
        if ($old_pw == $new_pw) {
            return json(['msg' => '新密码和旧密码一致', 'status' => 4]);
        }
        $res = AdminModel::where('account', $account)
            ->where('pw', md5($old_pw))
            ->field('id')
            ->find();
        if ($res) {
            $updateRes = AdminModel::where('id', $res['id'])
                ->update(['pw' => md5($new_pw)]);

            if ($updateRes) {
                return json(['msg' => '重置密码成功', 'status' => 1]);
            } else {
                return json(['msg' => '重置密码失败，稍后重试', 'status' => 2]);
            }
        }
        return json(['msg' => '密码或账号错误', 'status' => 5]);
    }

    /**
     * @api {post} /admin_forget_pw   通过密保修改密码
     * @apiGroup  admin
     * @apiParam {Number}   account     账号.
     * @apiParam {String}   question_pw  密保答案.
     * @apiParam {String}   new_pw  新密码.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：重置密码成功，2：密保答案不正确，请重新输入，3：参数验证失败，4：重置密码失败）
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function forgetPwByAnswer()
    {
        $rule     = [
            'account|用户账号'     => 'require|integer',
            'question_pw|密保答案' => 'require',
            'new_pw|新设置的密码'    => 'require|between:6,14'
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $account     = input('account');
        $question_pw = input('question_pw');
        $pw          = md5(input('pw'));

        $checkQuestionPW = AdminModel::where('account', $account)
            ->where('question_pw', $question_pw)
            ->find();

        if (!$checkQuestionPW) {
            return json(['msg' => '密保答案不正确，请重新输入', 'status' => 2]);
        } else {
            $updatePw = AdminModel::where('account', $account)
                ->update(['pw' => $pw]);
            if ($updatePw) {
                return json(['msg' => '重置密码成功', 'status' => 1]);
            } else {
                return json(['msg' => '重置密码失败', 'status' => 4]);
            }
        }
    }


    /**
     * @api {get} /admin_get_question   获取密保问题
     * @apiGroup  admin
     * @apiParam {Number}   account     账号.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：获取密保问题成功，2：失败，3：参数验证失败）
     * @apiSuccessExample {json} Success-Response:
     * {
     * "msg": "获取密保问题成功",
     * "status": 1,
     * "data": {
     * "question": "你的名字？"
     * }
     * }
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getQuestion()
    {
        $rule     = [
            'account|用户账号' => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $account  = input('account');
        $question = AdminModel::where('account', $account)
            ->field('question')
            ->find();
        if ($question) {
            return json(['msg' => '获取密保问题成功', 'status' => 1, 'data' => $question]);
        } else {
            return json(['msg' => '获取密保问题失败', 'status' => 2, 'data' => $question]);
        }
    }
}
