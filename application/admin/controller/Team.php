<?php
/**
 * User: xiaoxc
 * Date: 2019/4/13
 * Time: 14:57
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
        $findPeople = AdminModel::where('id', $create_people_id)->find();
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
     * 根据球队管理管id获取所属球队列表
     *  2019/4/14 16:34
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

    //更新球队信息
    public function updateTeam()
    {
        return 'updateTeam';
    }


    /**
     *  解散球队，admin表中的is_admin进行处理，将is_admin-1操作
     *    2019/4/14 17:02
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
}
