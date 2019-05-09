<?php

namespace app\admin\model;

use think\Model;

/**
 * Class Match 比赛管理
 * @package app\admin\model
 */
class Match extends Model
{
    protected $table = 'team_match';
    protected $autoWriteTimestamp = 'datetime';
}