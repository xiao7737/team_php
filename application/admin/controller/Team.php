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

/**
 * Class Team 球队管理
 * @package app\admin\controller
 */
class Team extends Controller
{
    /**
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addTeam()
    {
        $team_name        = Request::param('team_name');
        $description      = Request::param('description');
        $create_people_id = Request::param('create_people_id');

        $data = TeamModel::where('team_name', $team_name)->find();
        if ($data) {
            return json(['msg' => '该球队名已经被注册，请换一个球队名吧！', 'status' => 4]);
        }
        $findPeople = AdminModel::where('id', $create_people_id)->find();
        if (!$findPeople) {
            return json(['msg' => '当前用户没有球队的权限', 'status' => 3]);
        }

        //创建球队成功后，更新admin表，将该用户的is_admin+1
        $res = Db::transaction(function () use ($team_name, $description, $create_people_id) {
            Db::table('team_team')->insert([
                'team_name'        => $team_name,
                'description'      => $description,
                'create_people_id' => $create_people_id,
            ]);
            Db::table('team_admin')->where('id', $create_people_id)->setInc('is_admin');
        });
        if (!$res) {
            return json(['msg' => '创建球队失败', 'status' => 2]);
        }
        return json(['msg' => '创建球队成功', 'status' => 1]);
    }

    //根据球队管理管id获取所属球队列表
    public function getTeamList()
    {
        return 'getTeamList';
    }

    //更新球队信息
    public function updateTeam()
    {
        return 'updateTeam';
    }

    //解散球队，admin表中的is_admin进行处理，将is_admin-1操作
    public function deleteTeam()
    {
        return 'deleteTeam';
    }
}
