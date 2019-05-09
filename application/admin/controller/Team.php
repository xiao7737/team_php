<?php

namespace app\admin\controller;

use app\admin\model\Member;
use think\Controller;
use app\admin\model\Team as TeamModel;
use app\admin\model\Admin as AdminModel;
use think\Db;
use think\facade\Request;
use think\facade\Validate;

/**
 * Class Team 球队管理
 * @package app\admin\controller
 */
class Team extends Controller
{
    /**
     * @api {post} /team/add_team  新建球队
     * @apiGroup  team
     * @apiParam {String}   team_name     球队名.
     * @apiParam {String}   description  球队简介.
     * @apiParam {Number}   create_people_id  创建人id.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码：1：创建球队成功，2：创建球队失败，3：参数验证失败，5：当前用户没有球队的权限，4：该球队名已经被注册
     * @apiSuccessExample {json} Success-Response:
     * {
     * "msg": "创建球队成功",
     * "status": 1
     * }
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addTeam()
    {
        $rule     = [
            'team_name|球队名'          => 'require',
            'description|球队描述'       => 'require',
            'create_people_id|创建人id' => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $team_name        = Request::param('team_name');
        $description      = Request::param('description');
        $create_people_id = Request::param('create_people_id');

        $data = TeamModel::where('team_name', $team_name)->find();
        if ($data) {
            return json(['msg' => '该球队名已经被注册，请换一个球队名吧！', 'status' => 4]);
        }

        //球员不能创建球队
        $findPeople = AdminModel::where('id', $create_people_id)->where('is_admin', '<>', 0)->find();
        if (!$findPeople) {
            return json(['msg' => '当前用户没有创建球队的权限', 'status' => 5]);
        }

        Db::startTrans();
        try {
            $team = new TeamModel();
            $team->save([
                'team_name'        => $team_name,
                'description'      => $description,
                'create_people_id' => $create_people_id,
            ]);
            //判断原始状态是否>=0,如果为-1，直接变为1
            if ($findPeople['is_admin'] >= 0) {
                Db::table('team_admin')
                    ->where('id', $create_people_id)
                    ->setInc('is_admin');
            } else {      //初始态为-1
                Db::table('team_admin')
                    ->where('id', $create_people_id)
                    ->update(['is_admin' => Db::raw('is_admin+2')]);
            }
            Db::commit();
            return json(['msg' => '创建球队成功', 'status' => 1]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['msg' => '创建球队失败', 'status' => 2]);
        }
    }


    /**
     * @api {post} /team/get_team_list  获取球队列表
     * @apiGroup  team
     * @apiParam {Number}   user_id     创建人id.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：获取成功，2：参数验证失败）
     * @apiSuccessExample {json} Success-Response:
     * {
     * "msg": "获取成功",
     * "status": 1,
     * "data": [
     * {
     * "id": 7,
     * "team_name": "物联网1",
     * "description": "测试添加数据",
     * "create_date": "2019-04-14 16:06:49"
     * },
     * {
     * "id": 8,
     * "team_name": "物联网2",
     * "description": "测试添加数据2",
     * "create_date": "2019-04-14 16:09:21"
     * }
     * ]
     * }
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTeamList()
    {
        $rule     = [
            'user_id|用户id' => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 2]);
        }
        $user_id   = input('user_id');
        $team_list = TeamModel::where('create_people_id', $user_id)
            ->field('id, team_name, description, create_time as create_date')
            ->select();

        return json(['msg' => '获取成功', 'status' => 1, 'data' => $team_list]);
    }


    /**
     * @api {post} /team/update_team  编辑球队
     * @apiGroup  team
     * * @apiParam {Number}   id     球队编号.
     * @apiParam {String}   team_name     球队名.
     * @apiParam {String}   description  球队简介.
     * @apiParam {Number}   user_id    用户编号.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码：1：成功，2：失败，3：参数验证失败，4：该球队名已经被注册
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function updateTeam()
    {
        $rule     = [
            'id|球队编号'          => 'require|integer',
            'team_name|球队名'    => 'require',
            'description|球队描述' => 'require',
            'user_id|创建人id'    => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $id               = Request::param('id');
        $team_name        = Request::param('team_name');
        $description      = Request::param('description');
        $create_people_id = Request::param('user_id');

        $data = TeamModel::where('team_name', $team_name)->find();
        if ($data) {
            return json(['msg' => '该球队名已经被注册，请换一个球队名吧！', 'status' => 4]);
        }

        $res = TeamModel::where('id', $id)->update([
            'team_name'        => $team_name,
            'description'      => $description,
            'create_people_id' => $create_people_id,
        ]);

        $team_info = TeamModel::where('id', $id)->find();

        if ($res) {
            return json(['msg' => '编辑成功', 'status' => 1, 'data' => $team_info]);
        } else {
            return json(['msg' => '编辑失败，稍后重试', 'status' => 2, 'data' => $team_info]);
        }
    }


    /**
     * @api {post} /team/delete_team  删除球队
     * @apiGroup  team
     * @apiParam {Number}   user_id  创建人id.
     * @apiParam {Number}   team_id  球队id.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：删除球队成功，2：删除球队失败，稍后重试，3：参数验证失败）
     * @apiSuccessExample {json} Success-Response:
     * {
     * "msg": "删除球队成功",
     * "status": 1
     * }
     * @return \think\response\Json
     */
    public function deleteTeam()
    {
        $rule     = [
            'user_id|用户id' => 'require|integer',
            'team_id|球队id' => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $user_id = input('user_id');
        $team_id = input('team_id');
        Db::startTrans();
        try {
            TeamModel::where('create_people_id', $user_id)
                ->where('id', $team_id)
                ->delete();
            AdminModel::where('id', $user_id)
                ->setDec('is_admin');
            Db::commit();
            return json(['msg' => '删除球队成功', 'status' => 1]);
        } catch (\Exception $e) {
            Db::rollback();
            return json(['msg' => '删除球队失败，稍后重试', 'status' => 2]);
        }
    }

    /**
     * @api {post} /team/get_team_by_id  根据id获取球队信息
     * @apiGroup  team
     * @apiParam {Number}   user_id     创建人id.
     * @apiParam {Number}   id     球队id.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：获取成功，2：参数验证失败）
     * @apiSuccessExample {json} Success-Response:
     * {
     * "msg": "获取成功",
     * "status": 1,
     * "data": {
     * "id": 7,
     * "team_name": "物联网1",
     * "description": "测试添加数据",
     * "create_date": "2019-04-14 16:06:49"
     * }
     * }
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTeamById()
    {
        $rule     = [
            'user_id|用户id' => 'require|integer',
            'id|球队id'      => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 2]);
        }
        $id        = input('id');
        $user_id   = input('user_id');
        $team_data = TeamModel::where('create_people_id', $user_id)
            ->where('id', $id)
            ->field('id, team_name, description, create_time as create_date')
            ->find();

        return json(['msg' => '获取成功', 'status' => 1, 'data' => $team_data]);
    }

    /**
     * @api {post} /team/get_team_list_by_fuzzy  根据球队名模糊查询
     * @apiGroup  team
     * @apiParam  {String} team_name  球队名.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：获取成功，2：参数验证失败）
     * @apiSuccessExample {json} Success-Response:
     * {
     * "msg": "获取成功",
     * "status": 1,
     * "data": [
     * {
     * "id": 7,
     * "team_name": "物联网1",
     * "description": "测试添加数据",
     * "create_date": "2019-04-14 16:06:49"
     * },
     * {
     * "id": 8,
     * "team_name": "物联网2",
     * "description": "测试添加数据2",
     * "create_date": "2019-04-14 16:09:21"
     * }
     * ]
     * }
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTeamListByFuzzy()
    {
        $rule     = [
            'team_name|球队名' => 'require',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 2]);
        }
        $team_name = input('team_name');
        $team_list = TeamModel::where('team_name', 'like', '%' . $team_name . '%')
            ->field('id, team_name, description, create_time as create_date')
            ->select();

        return json(['msg' => '获取成功', 'status' => 1, 'data' => $team_list]);
    }


    /**
     * @api {get} /team/get_member_list  查看球员列表
     * @apiGroup  team
     * @apiParam  {Number} team_id  球队编号.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：获取成功，2：参数验证失败）
     * @apiSuccess {Number} user_id  球员编号
     * @apiSuccess {String} member_name 球员名字
     * @apiSuccess {Number} number 球衣号码
     * @apiSuccess {String} create_time 加入球队时间
     * @apiSuccessExample {json} Success-Response:
     * {
     * "msg": "获取成功",
     * "status": 1,
     * "data": [
     * {
     * "id": 2,
     * "user_id": "9",
     * "member_name": "张三",
     * "number": "22",
     * "create_time": "2019-04-14 16:06:49"
     * },
     * {
     * "id": 3,
     * "user_id": "8",
     * "member_name": "李四",
     * "number": "23",
     * "create_time": "2019-04-14 16:06:49"
     * }
     * ]
     * }
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMemberList()
    {
        $rule     = [
            'team_id|球队编号' => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 2]);
        }
        $team_id     = input('team_id');
        $member_info = Member::where('team_id', $team_id)
            ->field('id, user_id, member_name, number ,create_time')
            ->select();

        return json(['msg' => '获取成功', 'status' => 1, 'data' => $member_info]);
    }
}
