<?php

namespace app\admin\controller;

use app\admin\model\Member;
use think\Controller;
use think\facade\Validate;
use app\admin\model\Apply as ApplyModel;
use app\admin\model\Member as MemberModel;

/**
 * 申请管理：申请加入球队，球队管理员审批
 * Class Apply
 * @package app\admin\controller
 */
class Apply extends Controller
{
    /**
     * @api {post} /apply/add_apply  申请加入球队
     * @apiGroup  apply
     * @apiParam {Number}   user_id  用户编号.
     * @apiParam {Number}   team_id  球队编号.
     * @apiParam {Number}   apply_people  申请人名字.
     * @apiParam {Number}   apply_reason  申请详情.
     * @apiParam {Number}   apply_number  申请的球衣号码.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码：1：申请成功，2：申请失败，3：参数验证失败，4：申请的号码重复
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function applyJoinTeam()
    {
        $rule     = [
            'user_id|用户id'          => 'require|integer',
            'team_id|球队编号'          => 'require|integer',
            'apply_people|申请人'      => 'require',
            'apply_reason|申请加入球队详情' => 'require|integer',
            'apply_number|申请的球衣号码'  => 'require|integer'
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $user_id      = input('user_id');
        $team_id      = input('team_id');
        $apply_people = input('apply_people');
        $apply_reason = input('apply_reason');
        $apply_number = input('apply_number');

        //查询申请的球队中是否有重复的球衣号码
        $is_exist = MemberModel::where('team_id', $team_id)->where('number', $apply_number)->find();
        if ($is_exist) {
            return json(['msg' => '申请的球衣号码重复，请重新选择', 'status' => 4]);
        }

        $apply = new ApplyModel();
        $res   = $apply->save([
            'user_id'      => $user_id,
            'team_id'      => $team_id,
            'apply_people' => $apply_people,
            'apply_reason' => $apply_reason,
            'apply_number' => $apply_number,
        ]);

        if ($res) {
            return json(['msg' => '提交申请成功', 'status' => 1]);
        } else {
            return json(['msg' => '提交申请失败，请稍后重试', 'status' => 2]);
        }
    }

    //球队管理员查看申请列表，包括已经拒绝/已经同意的列表
    public function getApplyList()
    {
    }

    //球队管理员同同意或者拒绝申请，同意则加入球员表
    public function updateApplyStatus()
    {
        $rule     = [
            'id|申请编号'           => 'require|integer',
            'action|同意或者拒绝的状态码' => 'require|integer',         //1同意，2拒绝
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $id = input('id');
        $action = input('action');
        //开启事务
    }
}
