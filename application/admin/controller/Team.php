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
            return json(['msg' => '当前用户没有创建球队的权限', 'status' => 3]);
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
