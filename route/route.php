<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});

Route::get('hello/:name', 'index/hello');

Route::post('admin_register', 'admin/admin/register');
Route::post('admin_login', 'admin/admin/login');//->allowCrossDomain();  //设置跨域访问
Route::post('admin_update_pw', 'admin/admin/updatePw');         //管理员修改密码
Route::get('admin_get_question', 'admin/admin/getQuestion');    //获取密保问题
Route::post('admin_forget_pw', 'admin/admin/forgetPw');         //通过密保重置密码


Route::group('team', function () {
    Route::post('add_team', 'admin/team/addTeam');          //创建球队
    Route::post('delete_team', 'admin/team/deleteTeam');    //删除球队
    Route::post('update_team', 'admin/team/updateTeam');    //更新球队
    Route::post('get_team_list', 'admin/team/getTeamList'); //获取球队列表
});


return [

];
