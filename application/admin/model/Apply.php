<?php

namespace app\admin\model;

use think\Model;

/**
 * Class Apply 申请管理
 * @package app\admin\model
 */
class Apply extends Model
{
    protected $table = 'team_apply';
    protected $autoWriteTimestamp = 'datetime';
}
