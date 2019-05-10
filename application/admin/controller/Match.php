<?php

namespace app\admin\controller;

use think\Controller;
use app\admin\model\Match as MatchModel;
use think\facade\Validate;

/**
 * Class Match
 * 球队的比赛管理
 * @package app\admin\controller
 */
class Match extends Controller
{
    /**
     * @api {post} /match/add_match  添加比赛
     * @apiGroup  match
     * @apiParam {Number}   team_id        球队编号.
     * @apiParam {String}   match_team     对抗的球队名.
     * @apiParam {String}   match_time     对抗时间.
     * @apiParam {String}   match_address  对抗地点.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：成功，2：失败，3：参数验证失败）
     * @return \think\response\Json
     */
    public function addMatch()
    {
        $rule     = [
            'team_id|球队编号'       => 'require|integer',
            'match_team|比赛队伍'    => 'require',
            'match_time|比赛时间'    => 'require',
            'match_address|比赛地点' => 'require',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $team_id       = input('team_id');
        $match_team    = input('match_team');
        $match_time    = input('match_time');
        $match_address = input('match_address');

        $match = new MatchModel();
        $res   = $match->save([
            'team_id'       => $team_id,
            'match_team'    => $match_team,
            'match_time'    => $match_time,
            'match_address' => $match_address,
        ]);

        if ($res) {
            return json(['msg' => '添加比赛成功', 'status' => 1]);
        } else {
            return json(['msg' => '添加比赛失败', 'status' => 2]);
        }
    }

    /**
     * @api {post} /match/delete_match  删除比赛
     * @apiGroup  match
     * @apiParam {Number}   match_id   比赛id.
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
    public function deleteMatch()
    {
        $rule     = [
            'match_id|公告id' => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $match_id = input('match_id');

        $res = MatchModel::where('id', $match_id)
            ->delete();

        if ($res !== false) {
            return json(['msg' => '删除成功', 'status' => 1]);
        }
        return json(['msg' => '删除失败，稍后重试', 'status' => 2]);
    }


    /**
     * @api {get} /match/get_match_list  获取公告列表
     * @apiGroup  match
     * @apiParam {Number}   team_id     球队id.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：成功，列表按照创建时间倒序返回，2：参数验证失败）
     * @apiSuccess {String} match_team 对抗的队伍.
     * @apiSuccess {String} match_time 对抗的时间.
     * @apiSuccess {String} match_address 对抗的地址.
     * @apiSuccessExample {json} Success-Response:
     *{
     * "msg": "获取成功",
     * "status": 1,
     * "data": [
     * {
     * "id": 2,
     * "match_team": "勇往直前队",
     * "match_address": "体育馆",
     * "match_time": "2019-05-05 19:14:15"
     * }
     * ]
     * }
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMatchList()
    {
        $rule     = [
            'team_id|球队id' => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 2]);
        }
        $team_id    = input('team_id');
        $match_list = MatchModel::where('team_id', $team_id)
            ->field('id, team_id, match_team, match_time, match_address')
            ->order('create_time', 'desc')
            ->select();

        return json(['msg' => '获取成功', 'status' => 1, 'data' => $match_list]);
    }
}
