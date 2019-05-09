<?php

Route::post('admin_register', 'admin/admin/register');
Route::post('admin_login', 'admin/admin/login');//->allowCrossDomain();  //设置跨域访问
Route::post('admin_update_pw', 'admin/admin/updatePw');                  //管理员修改密码
Route::get('admin_get_question', 'admin/admin/getQuestion');             //获取密保问题
Route::post('admin_forget_pw', 'admin/admin/forgetPwByAnswer');          //通过密保重置密码

//用户管理
Route::group('user', function () {
    Route::get('get_user_info', 'admin/User/getUserInfo');             //获取用户信息
    Route::post('update_user_info', 'admin/User/updateUserInfo');      //编辑/添加用户信息
})->middleware('app\http\middleware\Auth::class');

//球队管理
Route::group('team', function () {
    Route::post('add_team', 'admin/team/addTeam');                           //创建球队
    Route::post('delete_team', 'admin/team/deleteTeam');                     //删除球队
    Route::post('update_team', 'admin/team/updateTeam');                     //更新球队
    Route::post('get_team_list', 'admin/team/getTeamList');                  //获取球队列表
    Route::post('get_team_by_id', 'admin/team/getTeamById');                 //获取球队信息
    Route::get('get_member_list', 'admin/team/getMemberList');               //获取球队的球员列表
    Route::post('get_team_list_by_fuzzy', 'admin/team/getTeamListByFuzzy');  //根据球队名模糊查询
})->middleware('app\http\middleware\Auth::class');

//公告管理
Route::group('notice', function () {
    Route::post('add_notice', 'admin/notice/addNotice');          //创建公告
    Route::post('delete_notice', 'admin/notice/deleteNotice');    //删除公告
    Route::post('update_notice', 'admin/notice/updateNotice');    //更新公告
    Route::get('get_notice_list', 'admin/notice/getNoticeList');  //获取公告列表
    Route::get('get_one_notice', 'admin/notice/getOneNotice');    //获取公告信息
})->middleware('app\http\middleware\Auth::class');

//荣耀管理
Route::group('honor', function () {
    Route::post('add_honor', 'admin/honor/addHonor');          //创建荣耀
    Route::post('delete_honor', 'admin/honor/deleteHonor');    //删除荣耀
    Route::post('update_honor', 'admin/honor/updateHonor');    //更新荣耀
    Route::get('get_honor_list', 'admin/honor/getHonorList');  //获取荣耀列表
})->middleware('app\http\middleware\Auth::class');

//申请管理
Route::group('apply', function () {
    Route::post('add_apply', 'admin/apply/applyJoinTeam');     //提交申请
    Route::post('update_apply', 'admin/apply/updateApply');    //审批申请
    Route::get('get_apply', 'admin/apply/getOneApply');        //获取申请结果
    Route::get('get_apply_list', 'admin/apply/getApplyList');  //获取申请列表
})->middleware('app\http\middleware\Auth::class');
