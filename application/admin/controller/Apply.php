<?php

namespace app\admin\controller;

use app\admin\model\Member;
use think\Controller;
use think\Db;
use think\facade\Validate;
use app\admin\model\Admin as AdminModel;
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
     * @apiSuccess {Number} status 状态码：1：申请成功，2：申请失败，3：参数验证失败，4：申请的号码重复，5：该用户没有申请加入球队的权限
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
            'apply_reason|申请加入球队详情' => 'require',
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
        //申请加入球队的用户，用户标识为-1
        $res = AdminModel::where('id', $user_id)->where('is_admin', -1)->find();
        if (!$res) {
            return json(['msg' => '该用户没有申请加入球队的权限', 'status' => 5]);
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


    /**
     * @api {post} /apply/update_apply  审批申请
     * @apiGroup  apply
     * @apiParam {Number}   id  申请编号.
     * @apiParam {Number}   action  审批操作：1同意，2拒绝.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码：1：成功，2：失败，3：参数验证失败
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function updateApply()
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
        $id     = input('id');
        $action = input('action');
        switch ($action) {
            case 2:
                ApplyModel::where('id', $id)->update(['status' => 3]);
                return json(['msg' => '操作成功', 'status' => 1]);
                break;
            case 1:
                //step1  从申请表获取申请相关信息
                $member_info = ApplyModel::where('id', $id)
                    ->field('user_id, team_id, apply_number, apply_people')->find();

                Db::startTrans();
                try {
                    //step2  更新申请表的申请状态
                    ApplyModel::where('id', $id)
                        ->update(['status' => 1]);

                    //step3  更新用户表的用户标识
                    AdminModel::where('id', $member_info['user_id'])
                        ->update(['is_admin' => 1]);

                    //step4  将申请人信息加入到球队成员表
                    $member = new MemberModel();
                    $member->save([
                            'user_id'     => $member_info['user_id'],
                            'team_id'     => $member_info['team_id'],
                            'number'      => $member_info['apply_number'],
                            'member_name' => $member_info['apply_people'],
                        ]
                    );
                    Db::commit();
                    return json(['msg' => '操作成功', 'status' => 1]);
                } catch (\Exception $e) {
                    Db::rollback();
                    return json(['msg' => '操作失败，稍后重试', 'status' => 2]);
                }
                break;
            default:
                return json(['msg' => '错误的请求，操作失败', 'status' => 2]);
        }
    }


    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getApplyList()
    {
        $rule     = [
            'team_id|球队编号' => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $team_id = input('team_id');

        //todo 对三种状态的申请分组
        $applyInfo = ApplyModel::where('team_id', $team_id)->select();
        return json(['msg' => "获取成功", 'status' => 1, 'data' => $applyInfo]);
    }


    /**
     * @api {get} /apply/getOneApply  用户查看审批详情
     * @apiGroup  apply
     * @apiParam {Number}   user_id  用户编号.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码：1：成功，3：参数验证失败
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOneApply()
    {
        $rule     = [
            'user_id|用户编号' => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $user_id = input('user_id');

        $applyInfo = ApplyModel::where('user_id', $user_id)
            ->field('apply_people, apply_number, status, create_time as create_date')->find();

        return json(['msg' => "获取成功", 'status' => 1, 'data' => $applyInfo]);
    }
}
