<?php

namespace app\admin\controller;

use think\Controller;
use think\facade\Validate;
use app\admin\model\Apply as ApplyModel;

/**
 *  申请管理：申请加入球队，球队管理员审批
 * Class Apply
 * @package app\admin\controller
 */
class Apply extends Controller
{
    /**
     *
     * @return \think\response\Json
     */
    public function applyJoinTeam()
    {
        $rule     = [
            'user_id'         => 'require|integer',
            'team_id'         => 'require|integer',
            'reason|申请加入球队详情' => 'require|integer',
            'number|申请的球衣号码'  => 'require|integer'
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $user_id = input('user_id');
        $team_id = input('team_id');
        $reason  = input('reason');
        $number  = input('number');

        //查询申请的球队中是否有重复的球衣号码
        $is_exist =

        $apply = new ApplyModel();
        $res   = $apply->save([
            'user_id' => $user_id,
            'team_id' => $team_id,
            'reason'  => $reason,
            'number'  => $number,
        ]);

        if ($res) {
            return json(['msg' => '提交申请成功', 'status' => 1]);
        } else {
            return json(['msg' => '提交申请失败，请稍后重试', 'status' => 2]);
        }
    }
}
