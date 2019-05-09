<?php

namespace app\admin\controller;

use think\Controller;
use app\admin\model\Admin as AdminModel;
use think\facade\Validate;

/**
 *  用户管理：用户编辑/添加个人信息，查看信息，
 * Class Admin
 * @package app\admin\controller
 */
class User extends Controller
{
    /**
     * @api {post} /user/update_user_info  编辑/添加用户信息
     * @apiGroup  user
     * @apiParam {Number}   id   用户id.
     * @apiParam {String}   name  名字.
     * @apiParam {String}   sex   性别.
     * @apiParam {String}   age  年龄.
     * @apiParam {String}   email  邮箱.
     * @apiParam {Number}   tel  联系方式.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：成功，2：失败，3：参数验证失败）
     * @apiSuccessExample {json} Success-Response:
     * {
     * "msg": "保存个人信息成功",
     * "status": 1
     * }
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function updateUserInfo()
    {
        $rule     = [
            'id|用户编号'  => 'require|integer',
            'name|姓名'  => 'require',
            'sex|性别'   => 'require',
            'age|年龄'   => 'require',
            'email|邮箱' => 'require',
            'tel|联系方式' => 'require',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $id    = input('id');
        $name  = input('name');
        $sex   = input('sex');
        $age   = input('age');
        $email = input('email');
        $tel   = input('tel');

        $res = AdminModel::where('id', $id)
            ->update([
                'name'  => $name,
                'age'   => $age,
                'sex'   => $sex,
                'email' => $email,
                'tel'   => $tel,
            ]);
        if ($res) {
            return json(['msg' => '保存个人信息成功', 'status' => 1]);
        } else {
            return json(['msg' => '保存个人信息失败', 'status' => 2]);
        }
    }

    /**
     * @api {get} /user/get_user_info  获取用户个人信息
     * @apiGroup  user
     * @apiParam {Number}   id     用户id.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：获取成功，2：参数验证失败）
     * @apiSuccessExample {json} Success-Response:
     *{
     * "msg": "获取成功",
     * "status": 1,
     * "data": {
     * "id": 4,
     * "name": "唐三",
     * "age": 24,
     * "sex": "男",
     * "email": "zhuzhu@126.com",
     * "tel": "13112341234"
     * }
     * }
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserInfo()
    {
        $rule     = [
            'id|用户编号' => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $id   = input('id');
        $data = AdminModel::where('id', $id)->field('id, name, age, sex, email, tel')->find();

        return json(['msg' => '获取成功', 'status' => 1, 'data' => $data]);
    }
}
