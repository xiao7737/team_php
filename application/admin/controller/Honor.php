<?php

namespace app\admin\controller;

use think\Controller;
use think\facade\Validate;
use app\admin\model\Honor as HonorModel;

/**
 * Class Honor 球队的获奖信息管理
 * @package app\admin\controller
 */
class Honor extends Controller
{
    /**
     * @api {post} /honor/add_honor  添加荣耀
     * @apiGroup  honor
     * @apiParam {Number}   team_id    球队编号
     * @apiParam {String}   match_name  比赛名称.
     * @apiParam {String}   prize_level  获得的名次或者称号.
     * @apiParam {Number}   honor_time   获得荣耀的时间.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码：1：添加荣耀成功，2：删除荣耀失败，3：参数验证失败
     * @apiSuccessExample {json} Success-Response:
     * {"msg":"添加荣耀成功","status":1}
     * @return \think\response\Json
     */
    public function addHonor()
    {
        $rule     = [
            'team_id|球队id'     => 'require|integer',
            'match_name|获奖名称'  => 'require',
            'prize_level|获奖等级' => 'require',
            'honor_time|获奖时间'  => 'require',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $team_id     = input('team_id');
        $match_name  = input('match_name');
        $prize_level = input('prize_level');
        $honor_time  = input('honor_time');

        $notice = new HonorModel();
        $res    = $notice->save([
            'team_id'     => $team_id,
            'match_name'  => $match_name,
            'prize_level' => $prize_level,
            'honor_time'  => $honor_time,
        ]);

        if ($res) {
            return json(['msg' => '添加荣耀成功', 'status' => 1]);
        } else {
            return json(['msg' => '添加荣耀失败', 'status' => 2]);
        }
    }

    /**
     * @api {post} /honor/delete_honor  删除荣耀
     * @apiGroup  honor
     * @apiParam {Number}   honor_id     荣耀id.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：成功，2：失败，3：参数验证失败）
     * @apiSuccessExample {json} Success-Response:
     * {
     * "msg": "删除成功",
     * "status": 1
     * }
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function deleteHonor()
    {
        $rule     = [
            'honor_id|荣耀编号' => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $honor_id = input('honor_id');

        $res = HonorModel::where('id', $honor_id)
            ->delete();

        if ($res !== false) {
            return json(['msg' => '删除成功', 'status' => 1]);
        }
        return json(['msg' => '删除失败，稍后重试', 'status' => 2]);
    }

    /**
     * @api {post} /honor/update_honor  编辑荣耀
     * @apiGroup  honor
     * @apiParam {Number}   honor_id    荣耀编号
     * @apiParam {Number}   team_id    球队编号
     * @apiParam {String}   match_name  比赛名称.
     * @apiParam {String}   prize_level  获得的名次或者称号.
     * @apiParam {Number}   honor_time   获得荣耀的时间戳.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码：1：更新成功，2：更新失败，3：参数验证失败
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function updateHonor()
    {
        $rule     = [
            'honor_id|荣耀编号'    => 'require|integer',
            'team_id|球队id'     => 'require|integer',
            'match_name|获奖名称'  => 'require',
            'prize_level|获奖等级' => 'require',
            'honor_time|获奖时间'  => 'require',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $honor_id    = input('honor_id');
        $team_id     = input('team_id');
        $match_name  = input('match_name');
        $prize_level = input('prize_level');
        $honor_time  = input('honor_time');

        $updateRes = HonorModel::where('id', $honor_id)
            ->update([
                'team_id'     => $team_id,
                'match_name'  => $match_name,
                'prize_level' => $prize_level,
                'honor_time'  => $honor_time,
            ]);

        if ($updateRes) {
            return json(['msg' => '更新成功', 'status' => 1]);
        } else {
            return json(['msg' => '更新失败', 'status' => 2]);
        }
    }

    /**
     * @api {get} /honor/get_honor_list  获取荣耀列表
     * @apiGroup  honor
     * @apiParam {Number}   team_id     球队编号.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：成功，列表按照更新时间倒序返回，2：参数验证失败）
     * @apiSuccess {String} match_name 比赛名称.
     * @apiSuccess {String} prize_level 比赛名次.
     * @apiSuccess {String}  honor_time 获得荣耀时间.
     * @apiSuccess {String}  update_date 更新时间.
     * @apiSuccessExample {json} Success-Response:
     * {
     * "msg": "获取成功",
     * "status": 1,
     * "data": [
     * {
     * "id": 6,
     * "match_name": "18年新生杯总决赛",
     * "prize_level": "冠军",
     * "honor_time": "1546272000",
     * "update_date": "2019-05-08 16:24:25"
     * },
     * {
     * "id": 5,
     * "match_name": "19年新生杯总决赛",
     * "prize_level": "冠军",
     * "honor_time": "1546272000",
     * "update_date": "2019-05-08 15:57:07"
     * }
     * ]
     * }
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getHonorList()
    {
        $rule     = [
            'team_id|球队编号' => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 2]);
        }
        $team_id    = input('team_id');
        $honor_list = HonorModel::where('team_id', $team_id)
            ->field('id, match_name, prize_level, honor_time, update_time as update_date')
            ->order('update_time', 'desc')
            ->select();

        return json(['msg' => '获取成功', 'status' => 1, 'data' => $honor_list]);
    }
}
