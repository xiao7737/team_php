<?php

namespace app\admin\controller;

use think\Controller;
use app\admin\model\Team as TeamModel;
use app\admin\model\Notice as NoticeModel;
use think\facade\Request;
use think\facade\Validate;

/**
 * Class Notice
 * 球队的公告管理
 * @package app\admin\controller
 */
class Notice extends Controller
{
    /**
     * @api {post} /notice/add_notice  添加公告
     * @apiGroup  notice
     * @apiParam {Number}   team_id     球队id.
     * @apiParam {Number}   user_id     创建人id.
     * @apiParam {String}   title  公告标题.
     * @apiParam {String}   content  公告内容.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：成功，2：失败，3：参数验证失败）
     * @return \think\response\Json
     */
    public function addNotice()
    {
        $rule     = [
            'user_id|创建人id' => 'require|integer',
            'team_id|球队id'  => 'require|integer',
            'title|公告标题'    => 'require',
            'content|公告内容'  => 'require',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $team_id = Request::param('team_id');
        $user_id = Request::param('user_id');
        $title   = Request::param('title');
        $content = Request::param('content');

        $notice = new NoticeModel();
        $res    = $notice->save([
            'title'   => $title,
            'content' => $content,
            'team_id' => $team_id,
            'user_id' => $user_id,
        ]);
        if ($res) {
            return json(['msg' => '添加公告成功', 'status' => 1]);
        } else {
            return json(['msg' => '添加公告失败', 'status' => 2]);
        }
    }

    /**
     * @api {post} /notice/delete_notice  删除公告
     * @apiGroup  notice
     * @apiParam {Number}   user_id     用户id.
     * @apiParam {Number}   notice_id   公告id.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：成功，2：失败，3：参数验证失败，4用户没有删除权限）
     * @apiSuccessExample {json} Success-Response:
     * {
     * "msg": "删除公告成功",
     * "status": 1
     * }
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function deleteNotice()
    {
        $rule     = [
            'user_id|用户id'   => 'require|integer',
            'notice_id|公告id' => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $user_id   = input('user_id');
        $notice_id = input('notice_id');

        $findNotice = NoticeModel::where('id', $notice_id)->find();
        $findTeam   = TeamModel::where('create_people_id', $user_id)->find();
        if ($findTeam['id'] != $findNotice['team_id']) {
            return json(['msg' => '该用户没有删除公告的权限', 'status' => 4]);
        }

        $res = NoticeModel::where('id', $notice_id)
            ->where('team_id', $findTeam['id'])
            ->delete();

        if ($res !== false) {
            return json(['msg' => '删除公告成功', 'status' => 1]);
        }
        return json(['msg' => '删除公告失败，稍后重试', 'status' => 2]);
    }


    /**
     * @api {get} /notice/get_notice_list  获取公告列表
     * @apiGroup  notice
     * @apiParam {Number}   team_id     球队id.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：成功，列表按照创建时间倒序返回，2：参数验证失败）
     * @apiSuccess {String} title 公告标题.
     * @apiSuccess {String} content 公告内容.
     * @apiSuccess {String} create_date 公告发布时间.
     * @apiSuccessExample {json} Success-Response:
     *{
     * "msg": "获取成功",
     * "status": 1,
     * "data": [
     * {
     * "id": 2,
     * "title": "22开会通知",
     * "content": "122月1日下午13点，体育馆全员开会",
     * "create_date": "2019-05-05 19:14:15"
     * }
     * ]
     * }
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getNoticeList()
    {
        $rule     = [
            'team_id|球队id' => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 2]);
        }
        $team_id     = input('team_id');
        $notice_list = NoticeModel::where('team_id', $team_id)
            ->field('id, title, content, update_time as create_date')
            ->order('create_time', 'desc')
            ->select();

        return json(['msg' => '获取成功', 'status' => 1, 'data' => $notice_list]);
    }

    /**
     * @api {get} /notice/get_one_notice  获取公告一条公告
     * @apiGroup  notice
     * @apiParam {Number}   notice_id     公告id.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：成功，2：参数验证失败）
     * @apiSuccessExample {json} Success-Response:
     * {
     * "msg": "获取成功",
     * "status": 1,
     * "data": {
     * "id": 2,
     * "title": "22开会通知",
     * "content": "122月1日下午13点，体育馆全员开会",
     * "create_date": "2019-05-05 19:14:15"
     * }
     * }
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOneNotice()
    {
        $rule     = [
            'notice_id|公告id' => 'require|integer',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 2]);
        }
        $notice_id = input('notice_id');
        $notice    = NoticeModel::where('id', $notice_id)
            ->field('id, title, content, update_time as create_date')
            ->find();

        return json(['msg' => '获取成功', 'status' => 1, 'data' => $notice]);
    }

    /**
     * @api {post} /notice/update_notice  编辑公告
     * @apiGroup  notice
     * @apiParam {Number}   notice_id     公告id.
     * @apiParam {Number}   user_id       创建人id.
     * @apiParam {Number}   team_id       球队id.
     * @apiParam {String}   title         公告标题.
     * @apiParam {String}   content       公告内容.
     * @apiSuccess {String} msg 详细信息.
     * @apiSuccess {Number} status 状态码（1：成功，2更新失败；3：参数验证失败）
     * @apiSuccessExample {json} Success-Response:
     * {
     * "msg": "编辑公告成功",
     * "status": 1
     * }
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function updateNotice()
    {
        $rule     = [
            'notice_id|公告id' => 'require|integer',
            'user_id|创建人id'  => 'require|integer',
            'team_id|球队id'   => 'require|integer',
            'title|公告标题'     => 'require',
            'content|公告内容'   => 'require',
        ];
        $validate = Validate::make($rule);
        $result   = $validate->check(input('param.'));
        if (!$result) {
            return json(['msg' => $validate->getError(), 'status' => 3]);
        }
        $notice_id = input('notice_id');
        $team_id   = input('team_id');
        $user_id   = input('user_id');
        $title     = input('title');
        $content   = input('content');

        $updateRes = NoticeModel::where('id', $notice_id)
            ->where('team_id', $team_id)
            ->where('user_id', $user_id)
            ->update([
                'title'   => $title,
                'content' => $content,
            ]);

        if ($updateRes) {
            return json(['msg' => '编辑公告成功', 'status' => 1]);
        } else {
            return json(['msg' => '编辑公告失败', 'status' => 2]);
        }
    }
}
