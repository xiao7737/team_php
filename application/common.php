<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 判断数组信息是否为空
 * 传入一个数组，循环遍历其中的值，如果存在
 * php中值为null的情况===》null（0,0.0,array(),空对象，被unset(),空字符串,赋值为null……）
 * 则返回1，不存在则返回0
 * @param $array //目标数组
 * @param int $num 默认参数
 * @return int  返回0，表示没有null，返回1，表示存在null
 */
function exists_null($array, $num = 0)
{
    foreach ($array as $value) {
        if (is_array($value)) {
            $num = exists_null($value, $num);
        } else {
            if (in_array(null, $array)) {
                return 1;
            }
        }
    }
    return $num;
}
