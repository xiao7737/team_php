<?php

namespace app\admin\model;

use think\Model;

/**
 * Class Member 球队成员管理
 * @package app\admin\model
 */
class Member extends Model
{
    protected $table = 'team_member';
    protected $autoWriteTimestamp = 'datetime';
}
