<?php
/**
 * Date: 2019/5/5
 * Time: 18:42
 * 　　   ┏┓　 ┏┓
 * 　┏━━━━┛┻━━━┛┻━━━┓
 * 　┃              ┃
 * 　┃       ━　    ┃
 * 　┃　  ┳┛ 　┗┳   ┃
 * 　┃              ┃
 * 　┃       ┻　    ┃
 * 　┗━━━┓      ┏━━━┛
 */

namespace app\admin\model;

use think\Model;

/**
 * Class Team 球队管理
 * @package app\admin\model
 */
class Notice extends Model
{
    protected $table = 'team_notice';
    protected $autoWriteTimestamp = 'datetime';
}