<?php

namespace app\admin\model;

use think\Model;

/**
 * Class Team 球队管理
 * @package app\admin\model
 */
class Team extends Model
{
    protected $table = 'team_team';
    protected $autoWriteTimestamp = 'datetime';
}