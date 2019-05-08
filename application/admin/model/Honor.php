<?php

namespace app\admin\model;

use think\Model;

/**
 * Class Honor 球队获奖管理
 * @package app\admin\model
 */
class Honor extends Model
{
    protected $table = 'team_honor';
    protected $autoWriteTimestamp = 'datetime';
}
