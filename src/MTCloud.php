<?php

/*
 * This file is part of the oiuv/talkfun-sdk.
 *
 * (c) oiuv <i@oiuv.cn>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Oiuv\TalkFunSdk;

/*=============================================================================
#     FileName: MTCloud.php
#         Desc: 欢拓开放API PHP SDK
#   LastChange: 2018-10-20 20:10:30
#      Version: 1.4.2
=============================================================================*/

use CURLFile;

/**
 *   欢拓语音视频服务开放接口SDK.
 */
class MTCloud
{
    /**
     *  合作方ID：欢拓平台的唯一ID.
     */
    private $openID = '';

    /**
     *  合作方秘钥：欢拓平台唯一ID对应的加密秘钥.
     */
    private $openToken = '';

    /**
     *   欢拓API接口地址
     */
    private $restUrl = 'http://api.talk-fun.com/portal.php';
    private $restUrl2 = 'http://api-1.talk-fun.com/portal.php';

    /**
     *   接口访问curl超时时间.
     */
    private $timeout = 10;

    /**
     *   返回的数据格式.
     */
    private $format = 'json';               //  json OR xml

    /**
     *   SDK版本号(请勿修改).
     */
    private $version = 'php.1.4';

    /**
     *   回调handler.
     */
    private $callbackHandler = null;

    /**
     *  API参数.
     */
    private $requestParam;

    /**
     *   状态码
     */
    const CODE_FAIL = -1;           //失败
    const CODE_SUCCESS = 0;         //成功的状态码,返回其它code均为失败
    const CODE_PARAM_ERROR = 10;    //参数错误
    const CODE_VIDEO_UPLOADED = 1281;  // 视频已上传过
    const CODE_SIGN_EXPIRE = 10002; //签名过期
    const CODE_SIGN_ERROR = 10003;  //签名验证错误

    /**
     *   用户支持的角色.
     */
    const ROLE_USER = 'user';           //普通用户
    const ROLE_ADMIN = 'admin';         //管理员，助教
    const ROLE_SPADMIN = 'spadmin';     //超级管理员，直播器用
    const ROLE_GUEST = 'guest';         //游客

    /**
     *   用户定义.
     */
    const USER_GENDER_UNKNOW = 0;       //未知生物
    const USER_GENDER_MALE = 1;         //男性
    const USER_GENDER_FEMALE = 2;       //女性

    /**
     *   主播账户类型.
     */
    const ACCOUNT_TYPE_MT = 1;       //欢拓账号类型
    const ACCOUNT_TYPE_THIRD = 2;       //合作方账号类型

    /**
     *   直播记录常量.
     */
    const LIVE_NO_PLAYBACK = 0;         //没有直播回放的记录
    const LIVE_HAS_PLAYBACK = 1;        //有直播回放的记录

    /**
     *   语音常量.
     */
    const VOICE_FLOW_CLOUD = 1;         //语音云模式
    const VOICE_FLOW_LISTEN_ONLY = 2;   //只听模式
    const VOICE_FLOW_AUTO = 2;          //自动模式，已弃用，和VOICE_FLOW_LISTEN_ONLY一样

    /**
     * 房间模式常量.
     */
    const ROOM_MODE_VOICE_CLOUD = 1;    //语音云模式
    const ROOM_MODE_BIG = 3;            //大班模式
    const ROOM_MODE_SMALL = 5;          //小班模式

    /**
     * 专辑类型.
     */
    const LIVE_ALBUM_TYPE_NORMAL = 0;           //普通专辑
    const LIVE_ALBUM_TYPE_NORMAL_CONCAT = 1;    //普通专辑 合并播放
    const LIVE_ALBUM_TYPE_SYSTEM = 10;          //系统专辑
    const LIVE_ALBUM_TYPE_SYSTEM_CONCAT = 11;   //系统专辑 合并播放

    /**
     * MTCloud constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        if (isset($config['openID'])) {
            $this->openID = trim($config['openID']);
        }

        if (isset($config['openToken'])) {
            $this->openToken = trim($config['openToken']);
        }
    }

    /**
     *   设置欢拓数据响应的格式.
     *
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     *   获取用户access_token,access_key,及房间地址(替代roomGetUrl 方法).
     *
     * @param string $uid      合作方的用户ID
     * @param string $nickname 合作方用户的名称
     * @param string $role     用户角色
     * @param string $roomid   进入的房间ID
     * @param int    $expire   有效期
     * @param array  $options  可选项，包括： gender:用户性别 , avatar:用户头像, gid:分组
     *
     * @return array
     */
    public function userAccess($uid, $nickname, $role, $roomid, $expire = 3600, $options = [])
    {
        $params = [
            'uid' => $uid,
            'nickname' => $nickname,
            'role' => $role,
            'roomid' => $roomid,
            'expire' => $expire,
            'options' => $options, ];

        return $this->call('user.access', $params);
    }

    /**
     * 获取用户access_token,access_key,及回放地址
     *
     * @param string $uid      合作方的用户ID
     * @param string $nickname 合作方用户的名称
     * @param string $role     用户角色
     * @param string $liveid   直播ID
     * @param int    $expire   有效期
     * @param array  $options  可选项，包括： gender:用户性别 , avatar:用户头像
     *
     * @return array
     */
    public function userAccessPlayback($uid, $nickname, $role, $liveid, $expire = 3600, $options = [])
    {
        $params = [
            'uid' => $uid,
            'nickname' => $nickname,
            'role' => $role,
            'liveid' => $liveid,
            'expire' => $expire,
            'options' => $options, ];

        return $this->call('user.access.playback', $params);
    }

    /**
     *   获取直播间地址
     *
     * @param string $uid      合作方的用户ID
     * @param string $nickname 合作方用户的名称
     * @param string $role     用户角色
     * @param string $roomid   进入的房间ID
     * @param int    $expire   有效期
     * @param array  $options  可选项，包括： gender:用户性别 , avatar:用户头像, gid:分组
     *
     * @return string
     */
    public function userAccessUrl($uid, $nickname, $role, $roomid, $expire = 3600, $options = [])
    {
        $accessAuth = $this->userAccessKey($uid, $nickname, $role, $roomid, $expire, $options);

        return 'http://open.talk-fun.com/room.php?accessAuth='.$accessAuth;
    }

    /**
     *   获取直播间验证key.
     *
     * @param string $uid      合作方的用户ID
     * @param string $nickname 合作方用户的名称
     * @param string $role     用户角色
     * @param string $roomid   进入的房间ID
     * @param int    $expire   有效期
     * @param array  $options  可选项，包括： gender:用户性别 , avatar:用户头像, gid:分组
     *
     * @return string
     */
    public function userAccessKey($uid, $nickname, $role, $roomid, $expire, $options = [])
    {
        $params = [
            'openID' => trim($this->openID),
            'timestamp' => $_SERVER['REQUEST_TIME'],
            'uid' => $uid,
            'nickname' => $nickname,
            'role' => $role,
            'roomid' => $roomid,
            'expire' => $expire,
            'options' => json_encode($options), ];

        $params['sign'] = $this->generateSign($params);
        $accessAuth = $this->base64UrlEncode(json_encode($params));

        return $accessAuth;
    }

    /**
     * 获取回放地址
     *
     * @param string $uid      合作方的用户ID
     * @param string $nickname 合作方用户的名称
     * @param string $role     用户角色
     * @param string $liveid   直播ID
     * @param int    $expire   有效期
     * @param array  $options  可选项，包括： gender:用户性别 , avatar:用户头像
     *
     * @return string
     */
    public function userAccessPlaybackUrl($uid, $nickname, $role, $liveid, $expire = 3600, $options = [])
    {
        $accessAuth = $this->userAccessPlaybackKey($uid, $nickname, $role, $liveid, $expire, $options);

        return 'http://open.talk-fun.com/player.php?accessAuth='.$accessAuth;
    }

    /**
     * 获取回放验证key.
     *
     * @param string $uid      合作方的用户ID
     * @param string $nickname 合作方用户的名称
     * @param string $role     用户角色
     * @param string $liveid   直播ID
     * @param int    $expire   有效期
     * @param array  $options  可选项，包括： gender:用户性别 , avatar:用户头像
     *
     * @return string
     */
    public function userAccessPlaybackKey($uid, $nickname, $role, $liveid, $expire, $options = [])
    {
        $params = [
            'openID' => trim($this->openID),
            'timestamp' => $_SERVER['REQUEST_TIME'],
            'uid' => $uid,
            'nickname' => $nickname,
            'role' => $role,
            'liveid' => $liveid,
            'expire' => $expire,
            'options' => json_encode($options), ];

        $params['sign'] = $this->generateSign($params);
        $accessAuth = $this->base64UrlEncode(json_encode($params));

        return $accessAuth;
    }

    /**
     * 获取用户access_token,access_key,及专辑地址
     *
     * @param string $uid      合作方的用户ID
     * @param string $nickname 合作方用户的名称
     * @param string $role     用户角色
     * @param string $album_id 专辑ID
     * @param int    $expire   有效期
     * @param array  $options  可选项，包括： gender:用户性别 , avatar:用户头像
     *
     * @return array
     */
    public function userAccessPlaybackAlbum($uid, $nickname, $role, $album_id, $expire = 3600, $options = [])
    {
        $params = [
            'uid' => $uid,
            'nickname' => $nickname,
            'role' => $role,
            'album_id' => $album_id,
            'expire' => $expire,
            'options' => $options, ];

        return $this->call('user.access.playbackAlbum', $params);
    }

    /**
     * 获取专辑播放地址
     *
     * @param string $uid      合作方的用户ID
     * @param string $nickname 合作方用户的名称
     * @param string $role     用户角色
     * @param string $album_id 专辑ID
     * @param int    $expire   有效期
     * @param array  $options  可选项，包括： gender:用户性别 , avatar:用户头像
     *
     * @return string
     */
    public function userAccessPlaybackAlbumUrl($uid, $nickname, $role, $album_id, $expire = 3600, $options = [])
    {
        $accessAuth = $this->userAccessPlaybackAlbumKey($uid, $nickname, $role, $album_id, $expire, $options);

        return 'http://open.talk-fun.com/player.php?accessAuth='.$accessAuth;
    }

    /**
     * 获取专辑播放验证key.
     *
     * @param string $uid      合作方的用户ID
     * @param string $nickname 合作方用户的名称
     * @param string $role     用户角色
     * @param string $album_id 专辑ID
     * @param int    $expire   有效期
     * @param array  $options  可选项，包括： gender:用户性别 , avatar:用户头像
     *
     * @return array
     */
    public function userAccessPlaybackAlbumKey($uid, $nickname, $role, $album_id, $expire, $options = [])
    {
        $params = [
            'openID' => trim($this->openID),
            'timestamp' => $_SERVER['REQUEST_TIME'],
            'uid' => $uid,
            'nickname' => $nickname,
            'role' => $role,
            'album_id' => $album_id,
            'expire' => $expire,
            'options' => json_encode($options), ];

        $params['sign'] = $this->generateSign($params);
        $accessAuth = $this->base64UrlEncode(json_encode($params));

        return $accessAuth;
    }

    /**
     * 获取用户access_token,access_key,及剪辑地址
     *
     * @param string $uid      合作方的用户ID
     * @param string $nickname 合作方用户的名称
     * @param string $role     用户角色
     * @param string $clipid   剪辑ID
     * @param int    $expire   有效期
     * @param array  $options  可选项，包括： gender:用户性别 , avatar:用户头像
     *
     * @return array
     */
    public function userAccessPlaybackClip($uid, $nickname, $role, $clipid, $expire = 3600, $options = [])
    {
        $params = [
            'uid' => $uid,
            'nickname' => $nickname,
            'role' => $role,
            'clipid' => $clipid,
            'expire' => $expire,
            'options' => $options, ];

        return $this->call('user.access.playbackClip', $params);
    }

    /**
     * 获取用户剪辑播放地址
     *
     * @param string $uid      合作方的用户ID
     * @param string $nickname 合作方用户的名称
     * @param string $role     用户角色
     * @param string $clipid   剪辑ID
     * @param int    $expire   有效期
     * @param array  $options  可选项，包括： gender:用户性别 , avatar:用户头像
     *
     * @return string
     */
    public function userAccessPlaybackClipUrl($uid, $nickname, $role, $clipid, $expire = 3600, $options = [])
    {
        $accessAuth = $this->userAccessPlaybackClipKey($uid, $nickname, $role, $clipid, $expire, $options);

        return 'http://open.talk-fun.com/player.php?accessAuth='.$accessAuth;
    }

    /**
     * 获取用户剪辑播放key.
     *
     * @param string $uid      合作方的用户ID
     * @param string $nickname 合作方用户的名称
     * @param string $role     用户角色
     * @param string $clipid   剪辑ID
     * @param int    $expire   有效期
     * @param array  $options  可选项，包括： gender:用户性别 , avatar:用户头像
     *
     * @return array
     */
    public function userAccessPlaybackClipKey($uid, $nickname, $role, $clipid, $expire, $options = [])
    {
        $params = [
            'openID' => trim($this->openID),
            'timestamp' => $_SERVER['REQUEST_TIME'],
            'uid' => $uid,
            'nickname' => $nickname,
            'role' => $role,
            'clipid' => $clipid,
            'expire' => $expire,
            'options' => json_encode($options), ];

        $params['sign'] = $this->generateSign($params);
        $accessAuth = $this->base64UrlEncode(json_encode($params));

        return $accessAuth;
    }

    /**
     *   获取在线用户列表 (时间区间间隔不大于7天).
     *
     * @param string $roomid     房间ID
     * @param string $start_time 查询起始时间,格式:2015-01-01 12:00:00
     * @param string $end_time   查询结束时间,格式:2015-01-01 13:00:00
     * @param int    $page
     * @param int    $size
     *
     * @return array
     */
    public function userOnlineList($roomid, $start_time, $end_time, $page = 1, $size = 10)
    {
        $params = [
            'roomid' => $roomid,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'page' => $page,
            'size' => $size, ];

        return $this->call('user.online.list', $params);
    }

    /**
     *   查询某个房间的状态信息.
     *
     * @param int $roomid 房间id
     *
     * @return array 房间信息
     */
    public function roomGetInfo($roomid)
    {
        $params['roomid'] = $roomid;

        return $this->call('room.getInfo', $params);
    }

    /**
     *   获取房间登录地址
     *
     * @param int $roomid 房间id
     *
     * @return array 房间登录地址
     */
    public function roomGetUrl($roomid)
    {
        $params['roomid'] = $roomid;

        return $this->call('room.getUrl', $params);
    }

    /**
     *   创建一个房间.
     *
     * @param string $roomName  房间名称
     * @param int    $voiceFlow 语音模式
     * @param string $authKey   管理员密码
     * @param int    $modetype  房间模式
     * @param array  $options   可选项，包括： barrage:弹幕开关
     *
     * @return array
     */
    public function roomCreate($roomName, $voiceFlow = self::VOICE_FLOW_LISTEN_ONLY, $authKey = '', $modetype = self::ROOM_MODE_BIG, $options = [])
    {
        $params['roomName'] = $roomName;
        $params['voiceFlow'] = $voiceFlow;
        $params['authKey'] = $authKey;
        $params['modetype'] = $modetype;
        $params['options'] = $options;

        return $this->call('room.create', $params);
    }

    /**
     *  根据合作方的账号，创建并且绑定一个房间.
     *
     * @param string $userUnique 合作方用户唯一账号
     * @param string $nickname   用户的昵称
     *
     * @return array
     */
    public function roomAutoCreate($userUnique, $nickname)
    {
        $params = [
            'userUnique' => $userUnique,
            'nickname' => $nickname, ];

        return $this->call('room.autocreate', $params);
    }

    /**
     *   更新房间信息.
     *
     * @param string $roomid 房间ID
     * @param array  $params 房间信息,包括： roomName:房间名称,modetype:房间模式，authKey:管理员密码， userKey:普通用户密码 ，barrage:弹幕开关 开:1 关:0
     *
     * @return array
     */
    public function roomUpdate($roomid, $params = [])
    {
        $params['roomid'] = $roomid;

        return $this->call('room.update', $params);
    }

    /**
     *   删除一个房间.
     *
     * @param string $roomid 房间ID
     *
     * @return array
     */
    public function roomDrop($roomid)
    {
        $params['roomid'] = $roomid;

        return $this->call('room.drop', $params);
    }

    /**
     *   获取房间列表
     *   按页码和每页数量，分页获取房间列表
     *   注意size不能太大，以免影响效率.
     *
     * @param int $page 页码
     * @param int $size 获取房间数量
     *
     * @return array
     */
    public function roomList($page = 1, $size = 10)
    {
        $params = [
            'page' => $page,
            'size' => $size, ];

        return $this->call('room.list', $params);
    }

    /**
     *   房间绑定主播账号.
     *
     * @param int    $roomid      房间ID
     * @param string $account     欢拓主播ID或合作方账号ID
     * @param int    $accountType 主播账户类型
     *
     * @return array
     */
    public function roomBindAccount($roomid, $account, $accountType = self::ACCOUNT_TYPE_MT)
    {
        $params['roomid'] = $roomid;
        $params['account'] = $account;
        $params['accountType'] = $accountType;

        return $this->call('room.live.bindAccount', $params);
    }

    /**
     *   取消房间绑定.
     *
     * @param int    $roomid      房间ID
     * @param string $account     欢拓主播ID或合作方账号ID
     * @param int    $accountType 主播账户类型
     *
     * @return array
     */
    public function roomUnbindAccount($roomid, $account, $accountType = self::ACCOUNT_TYPE_MT)
    {
        $params['roomid'] = $roomid;
        $params['account'] = $account;
        $params['accountType'] = $accountType;

        return $this->call('room.live.unbindAccount', $params);
    }

    /**
     *  发送广播.
     *
     * @param string $roomid  房间ID
     * @param string $cmd     指令
     * @param array  $args    广播内容
     * @param array  $options 广播选项
     *
     * @return array
     */
    public function roomBroadcastSend($roomid, $cmd, $args = [], $options = [])
    {
        $params = [
            'roomid' => $roomid,
            'cmd' => $cmd,
            'params' => $args,
            'options' => $options, ];

        return $this->call('room.broadcast.send', $params);
    }

    /**
     *  根据房间ID获取当前房间的在线人数.
     *
     * @param string $roomid 房间ID
     *
     * @return array
     */
    public function roomOnlineTotal($roomid)
    {
        $params = [
            'roomid' => $roomid, ];

        return $this->call('room.online.total', $params);
    }

    /**
     *  虚拟用户导入.
     *
     * @param string $roomid   房间ID
     * @param array  $userList 机器人列表，示例：[['nickname'=>'xxx', 'avatar'=>'xxx'], ['nickname'=>'xxxx', 'avatar'=>'xxx'], ......]
     * @param int    $total    机器人数量，不能超过10000
     *
     * @return array
     */
    public function roomAddRobot($roomid, $userList, $total = 0)
    {
        $params = [
            'roomid' => $roomid,
            'userList' => $userList,
            'total' => $total, ];

        return $this->call('room.robot.add', $params, 'POST');
    }

    /**
     * 滚动公告接口.
     *
     * @param string $roomid   房间ID
     * @param string $content  滚动公告内容
     * @param string $link     滚动公告链接
     * @param string $duration 滚动通知显示时长(单位：秒)
     *
     * @return array
     */
    public function roomNoticeRoll($roomid, $content, $link, $duration)
    {
        $params = [
            'roomid' => $roomid,
            'content' => $content,
            'link' => $link,
            'duration' => $duration, ];

        return $this->call('room.notice.roll', $params);
    }

    /**
     *   主播获取登录页面.
     *
     * @param string $account     主播账号
     * @param int    $accountType 账户类型
     * @param array  $options
     *
     * @return array
     */
    public function zhuboLogin($account, $accountType = self::ACCOUNT_TYPE_MT, $options = [])
    {
        $params['account'] = $account;
        $params['accountType'] = $accountType;
        $params['options'] = $options;

        return $this->call('zhubo.login', $params);
    }

    /**
     *  根据房间ID获取主播登录地址
     *
     * @param string $roomid  房间ID
     * @param array  $options
     *
     * @return array
     */
    public function zhuboRoomLogin($roomid, $options = [])
    {
        $params['roomid'] = $roomid;
        $params['options'] = $options;

        return $this->call('zhubo.room.login', $params);
    }

    /**
     *   查询主播信息.
     *
     * @param string $account     主播账号
     * @param int    $accountType 账号类型
     *
     * @return array
     */
    public function zhuboGet($account, $accountType = self::ACCOUNT_TYPE_MT)
    {
        $params['account'] = $account;
        $params['accountType'] = $accountType;

        return $this->call('zhubo.get', $params);
    }

    /**
     *   创建一个主播.
     *
     * @param string $account      合作方主播账户(可选)  当账户类型为合作方账户，填写此项
     * @param string $nickname     主播昵称
     * @param int    $accountType  账户类型
     * @param string $password     密码    (可选)  如果账户类型为欢拓账户，填写此项
     * @param string $intro        简介
     * @param int    $departmentID 部门ID
     *
     * @return array
     */
    public function zhuboCreate($account, $nickname, $accountType = self::ACCOUNT_TYPE_MT, $password = '', $intro = '', $departmentID = 0)
    {
        $params['nickname'] = $nickname;
        $params['accountType'] = $accountType;
        $params['account'] = $account;
        $params['password'] = $password;
        $params['intro'] = $intro;
        $params['departmentID'] = $departmentID;

        return $this->call('zhubo.create', $params);
    }

    /**
     *   更新主播信息.
     *
     * @param string $account      主播账号
     * @param int    $accountType  账户类型
     * @param string $nickname     昵称
     * @param string $intro        简介
     * @param int    $departmentID 部门ID
     *
     * @return array
     */
    public function zhuboUpdateInfo($account, $accountType, $nickname, $intro = '', $departmentID = 0)
    {
        $params['account'] = $account;
        $params['accountType'] = $accountType;
        $params['nickname'] = $nickname;
        $params['intro'] = $intro;
        $params['departmentID'] = $departmentID;

        return $this->call('zhubo.update.info', $params);
    }

    /**
     *   更新主播密码
     *
     * @param string $account     主播账号
     * @param int    $accountType 账号类型
     * @param string $password    密码
     *
     * @return array
     */
    public function zhuboUpdatePassword($account, $accountType, $password)
    {
        $params['account'] = $account;
        $params['accountType'] = $accountType;
        $params['password'] = $password;

        return $this->call('zhubo.update.password', $params);
    }

    /**
     *   删除一个主播.
     *
     * @param string $account     主播账户
     * @param int    $accountType 账户类型
     *
     * @return array
     */
    public function zhuboDel($account, $accountType = self::ACCOUNT_TYPE_MT)
    {
        $params['account'] = $account;
        $params['accountType'] = $accountType;

        return $this->call('zhubo.del', $params);
    }

    /**
     *   获取主播列表.
     *
     * @param int $page 页码
     * @param int $size 每页个数
     *
     * @return array
     */
    public function zhuboList($page = 1, $size = 10)
    {
        $params = [
            'page' => $page,
            'size' => $size, ];

        return $this->call('zhubo.list', $params);
    }

    /**
     *  修改主播头像.
     *
     * @param string $account     主播账号
     * @param int    $accountType 主播账号类型
     * @param string $filename    图片路径(支持图片格式:jpg、jpeg)
     *
     * @return array
     *
     * @throws MTCloudException
     */
    public function zhuboUpdatePortrait($account, $accountType, $filename)
    {
        $params = [
            'account' => $account,
            'accountType' => $accountType, ];
        $ret = $this->call('zhubo.portrait.uploadurl', $params);

        if (self::CODE_SUCCESS === $ret['code']) {
            $filename = realpath($filename);
            if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
                $params[$ret['data']['field']] = new CURLFile($filename);
            } else {
                $params[$ret['data']['field']] = '@'.$filename;
            }

            return $this->_request($ret['data']['api'], 'POST', $params);
        }

        return [
            'code' => self::CODE_FAIL,
            'msg' => '该主播不存在', ];
    }

    public function zhuboUpdateExt($account, $accountType, $ext)
    {
        $params = [
            'account' => $account,
            'accountType' => $accountType,
            'ext' => $ext, ];

        return $this->call('zhubo.update.ext', $params);
    }

    public function zhuboGetExt($account, $accountType)
    {
        $params = [
            'account' => $account,
            'accountType' => $accountType, ];

        return $this->call('zhubo.getExt', $params);
    }

    /**
     *  根据直播ID获取评分列表.
     *
     * @param int $liveid 直播ID
     *
     * @return array
     */
    public function scoreLiveList($liveid)
    {
        $params = [
            'liveid' => $liveid, ];

        return $this->call('score.live.list', $params);
    }

    /**
     *  根据主播ID获取评分列表.
     *
     * @param int $account     主播账号
     * @param int $accountType 主播账号类型
     * @param int $page        页码
     * @param int $size        每页个数
     *
     * @return array
     */
    public function scoreZhuboList($account, $accountType = self::ACCOUNT_TYPE_MT, $page = 1, $size = 10)
    {
        $params = [
            'account' => $account,
            'accountType' => $accountType,
            'page' => $page,
            'size' => $size, ];

        return $this->call('score.zhubo.list', $params);
    }

    /**
     *   获取某场直播的记录信息及回放地址
     *
     * @param int $liveid 直播记录ID
     * @param int $expire 回放地址有效时间
     *
     * @return array
     */
    public function liveGet($liveid, $expire = 3600)
    {
        $params = [
            'liveid' => $liveid,
            'expire' => $expire, ];

        return $this->call('live.get', $params);
    }

    /**
     *   批量获取直播记录及回放地址
     *
     * @param array $liveids 直播记录ID
     * @param int   $expire  回放地址有效时间
     *
     * @return array
     */
    public function liveGetBatch($liveids, $expire = 3600)
    {
        $params = [
            'liveids' => $liveids,
            'expire' => $expire, ];

        return $this->call('live.getBatch', $params);
    }

    /**
     *   获取最新的几个直播记录.
     *
     * @param int $size   每页个数
     * @param int $roomid 房间ID
     *
     * @return array
     */
    public function liveGetLast($size = 1, $roomid = 0)
    {
        $params = [
            'size' => $size,
            'roomid' => $roomid, ];

        return $this->call('live.getlast', $params);
    }

    /**
     *   根据日期获取直播记录列表.
     *
     * @param string $startDate 起始日期，格式为:yyyy-mm-dd
     * @param string $endDate   结束日期，格式为:yyyy-mm-dd
     * @param int    $page      页码
     * @param int    $size      每页条数
     * @param string $playback  是否上传了直播记录
     *
     * @return array
     */
    public function liveList($startDate, $endDate, $page = 1, $size = 10, $playback = '')
    {
        $params = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'page' => $page,
            'size' => $size,
            'playback' => $playback, ];

        return $this->call('live.list', $params);
    }

    /**
     *   获取全部直播记录列表.
     *
     * @param int    $page   页码(默认:1)
     * @param int    $size   每页个数(默认:10)
     * @param string $order  排序(desc:降序，asc:升序)
     * @param int    $roomid 房间ID
     *
     * @return array
     */
    public function liveListAll($page = 1, $size = 10, $order = 'desc', $roomid = 0)
    {
        $params = [
            'page' => $page,
            'size' => $size,
            'order' => $order,
            'roomid' => $roomid, ];

        return $this->call('live.listall', $params);
    }

    /**
     *  获取直播聊天列表.
     *
     * @param string $liveid 直播ID
     * @param int    $page   页码
     *
     * @return array
     */
    public function liveMessageList($liveid, $page = 1)
    {
        $params = [
            'liveid' => $liveid,
            'page' => $page, ];

        return $this->call('live.message', $params);
    }

    /**
     *  获取直播鲜花记录.
     *
     * @param string $liveid 直播ID
     * @param int    $page   页码(默认:1)
     * @param int    $size   每页个数(默认:10)
     *
     * @return array
     */
    public function liveFlowerList($liveid, $page = 1, $size = 10)
    {
        $params = [
            'liveid' => $liveid,
            'page' => $page,
            'size' => $size, ];

        return $this->call('live.flower.list', $params);
    }

    /**
     * 发起投票.
     *
     * @param int    $roomid   房间ID
     * @param string $uid      投票发布者，合作方用户ID
     * @param string $nickname 投票发布者，合作方用户昵称
     * @param string $title    投票主题
     * @param string $label    投票标签
     * @param string $op       选项，json格式，比如 ["aaa","bbb"]，aaa为第一个选项，bbb为第二个选项
     * @param int    $type     类型，0为单选，1为多选
     * @param int    $optional 若为单选则传1，多选则传的值为多少表示可以选几项
     * @param string $answer   答案，设置第几项为答案，传入 "0" 表示第一个选项为正确答案，传入 "0,2" 表示第一和第三项为正确答案，不设置答案则传空字符串
     * @param string $image    图片路径
     * @param array  $options  可选参数
     *
     * @return array
     */
    public function liveVoteAdd($roomid, $uid, $nickname, $title, $label, $op, $type, $optional, $answer = '', $image = '', $options = [])
    {
        $params = [
            'roomid' => $roomid,
            'uid' => $uid,
            'nickname' => $nickname,
            'title' => $title,
            'label' => $label,
            'op' => $op,
            'type' => $type,
            'optional' => $optional,
            'answer' => $answer,
            'options' => $options, ];

        $files = [];
        if (!empty($image)) {
            if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
                $files['image'] = new CURLFile($image);
            } else {
                $files['image'] = '@'.$image;
            }
        }

        return $this->call('live.vote.add', $params, 'POST', $files);
    }

    /**
     * 结束投票.
     *
     * @param int    $vid        投票ID
     * @param int    $showResult 是否显示投票结果，0为不显示，1为显示
     * @param string $uid        投票结束者，合作方用户ID
     * @param string $nickname   投票结束者，合作方用户昵称
     *
     * @return array
     */
    public function liveVoteEnd($vid, $showResult, $uid, $nickname)
    {
        $params = [
            'vid' => $vid,
            'showResult' => $showResult,
            'uid' => $uid,
            'nickname' => $nickname, ];

        return $this->call('live.vote.end', $params);
    }

    /**
     * 发布预发布的投票.
     *
     * @param int $vid    投票ID
     * @param int $roomid 房间ID
     *
     * @return array
     */
    public function liveVoteEmit($vid, $roomid)
    {
        $params = [
            'vid' => $vid,
            'roomid' => $roomid, ];

        return $this->call('live.vote.emit', $params);
    }

    /**
     * 删除投票.
     *
     * @param int $vid 投票ID
     *
     * @return array
     */
    public function liveVoteDelete($vid)
    {
        $params = [
            'vid' => $vid, ];

        return $this->call('live.vote.delete', $params);
    }

    /**
     * 更新投票.
     *
     * @param int   $vid     投票ID
     * @param array $options 要更新的信息
     *
     * @return array
     */
    public function liveVoteUpdate($vid, $options)
    {
        $params = [
            'vid' => $vid,
            'options' => $options, ];

        $files = [];
        if (isset($options['image']) && !empty($options['image'])) {
            if (file_exists($options['image'])) {
                if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
                    $files['image'] = new CURLFile($options['image']);
                } else {
                    $files['image'] = '@'.$options['image'];
                }
            } else {
                return [
                    'code' => self::CODE_FAIL,
                    'msg' => '文件'.$options['image'].'不存在', ];
            }
        }

        return $this->call('live.vote.update', $params, 'POST', $files);
    }

    /**
     * 获取直播流地址
     *
     * @param int   $liveid  直播ID
     * @param array $options 可选参数
     *
     * @return array
     */
    public function liveStreamAddress($liveid, $options = [])
    {
        $params = [
            'liveid' => $liveid,
            'options' => $options, ];

        return $this->call('live.streamAddress', $params);
    }

    /**
     * 发起提问.
     *
     * @param int    $roomid   房间ID
     * @param string $content  提问内容
     * @param string $uid      用户id
     * @param string $role     用户角色
     * @param string $nickname 用户昵称
     * @param array  $options  可选参数
     *
     * @return array
     */
    public function liveQaAdd($roomid, $content, $uid, $role, $nickname, $options = [])
    {
        $params = [
            'roomid' => $roomid,
            'content' => $content,
            'uid' => $uid,
            'role' => $role,
            'nickname' => $nickname,
            'options' => $options, ];

        return $this->call('live.qa.add', $params);
    }

    /**
     * 审核通过提问.
     *
     * @param int $qid    提问ID
     * @param int $roomid 房间ID
     *
     * @return array
     */
    public function liveQaAudit($qid, $roomid)
    {
        $params = [
            'qid' => $qid,
            'roomid' => $roomid, ];

        return $this->call('live.qa.audit', $params);
    }

    /**
     * 删除提问.
     *
     * @param int $qid    提问ID
     * @param int $roomid 房间ID
     *
     * @return array
     */
    public function liveQaDelete($qid, $roomid)
    {
        $params = [
            'qid' => $qid,
            'roomid' => $roomid, ];

        return $this->call('live.qa.delete', $params);
    }

    /**
     * 回复提问.
     *
     * @param int    $qid      提问ID
     * @param int    $roomid   房间ID
     * @param string $content  回复内容
     * @param string $uid      用户ID
     * @param string $nickname 用户昵称
     * @param array  $options  可选参数
     *
     * @return array
     */
    public function liveQaAnswer($qid, $roomid, $content, $uid, $nickname, $options = [])
    {
        $params = [
            'qid' => $qid,
            'roomid' => $roomid,
            'content' => $content,
            'uid' => $uid,
            'nickname' => $nickname,
            'options' => $options, ];

        return $this->call('live.qa.answer', $params);
    }

    /**
     * 获取问答列表.
     *
     * @param int   $roomid  房间ID
     * @param array $options 可选参数
     *
     * @return array
     */
    public function liveQaList($roomid, $options = [])
    {
        $params = [
            'roomid' => $roomid,
            'options' => $options, ];

        return $this->call('live.qa.list', $params);
    }

    /**
     * 获取提问总数.
     *
     * @param string $liveid 直播ID
     *
     * @return array
     */
    public function liveQaTotal($liveid)
    {
        $params = [
            'liveid' => $liveid, ];

        return $this->call('live.qa.total', $params);
    }

    /**
     *   创建一个专辑.
     *
     * @param string $album_name 专辑名称
     * @param array  $liveids    直播ID
     * @param int    $album_type
     *
     * @return array
     */
    public function albumCreate($album_name, $liveids = [], $album_type = self::LIVE_ALBUM_TYPE_NORMAL_CONCAT)
    {
        $params = [
            'album_name' => $album_name,
            'liveids' => $liveids,
            'album_type' => $album_type, ];

        return $this->call('album.create', $params);
    }

    /**
     *   获取一个直播专辑.
     *
     * @param string $album_id 专辑ID
     * @param int    $expire   地址有效时间
     *
     * @return array
     */
    public function albumGet($album_id, $expire = 3600)
    {
        $params = [
            'album_id' => $album_id,
            'expire' => $expire, ];

        return $this->call('album.get', $params);
    }

    /**
     *   删除一个专辑.
     *
     * @param string $album_id 专辑ID
     *
     * @return array
     */
    public function albumDelete($album_id)
    {
        $params = [
            'album_id' => $album_id, ];

        return $this->call('album.delete', $params);
    }

    /**
     *   往专辑增加一个回放记录.
     *
     * @param string $album_id 专辑ID
     * @param array  $liveids  回放记录的id
     *
     * @return array
     */
    public function albumAdd($album_id, $liveids = [])
    {
        $params = [
            'album_id' => $album_id,
            'liveids' => $liveids, ];

        return $this->call('album.add', $params);
    }

    /**
     *   从专辑里面清除某个回放.
     *
     * @param int   $album_id 专辑ID
     * @param array $liveids  回放记录的id
     *
     * @return array
     */
    public function albumRemove($album_id, $liveids = [])
    {
        $params = [
            'album_id' => $album_id,
            'liveids' => $liveids, ];

        return $this->call('album.remove', $params);
    }

    /**
     *   创建一个课程专辑.
     *
     * @param string $album_name 专辑名称
     * @param array  $course_ids 课程id
     *
     * @return array
     */
    public function albumCreateCourse($album_name, $course_ids = [])
    {
        $params = [
            'album_name' => $album_name,
            'course_ids' => $course_ids, ];

        return $this->call('album.course.create', $params);
    }

    /**
     *   往课程专辑增加一个课程回放记录.
     *
     * @param string $album_id   专辑ID
     * @param array  $course_ids 课程回放记录ID列表
     *
     * @return array
     */
    public function albumAddCourse($album_id, $course_ids = [])
    {
        $params = [
            'album_id' => $album_id,
            'course_ids' => $course_ids, ];

        return $this->call('album.course.add', $params);
    }

    /**
     *   从课程专辑里面清除某个课程回放.
     *
     * @param int   $album_id   专辑ID
     * @param array $course_ids 回放记录的课程id
     *
     * @return array
     */
    public function albumRemoveCourse($album_id, $course_ids = [])
    {
        $params = [
            'album_id' => $album_id,
            'course_ids' => $course_ids, ];

        return $this->call('album.course.remove', $params);
    }

    /**
     * 根据房间及时间获取回放记录.
     *
     * @param string $roomid     房间ID
     * @param string $start_time 开始时间 格式:2014-12-26 12:00:00
     * @param int expire 地址有效期
     *
     * @return array
     */
    public function liveRoomGet($roomid, $start_time, $expire = 3600)
    {
        $params = [
            'roomid' => $roomid,
            'start_time' => $start_time,
            'expire' => $expire, ];

        return $this->call('live.room.get', $params);
    }

    /**
     * 根据房间及时间区间获取回放记录.
     *
     * @param string $roomid     房间ID
     * @param string $start_time 起始区间时间  格式：2014-12-26 00:00:00
     * @param string $end_time   结束区间时间  格式: 2014-12-26 12:00:00
     * @param int expire 有效期
     *
     * @return array
     */
    public function liveRoomList($roomid, $start_time, $end_time, $expire = 3600)
    {
        $params = [
            'roomid' => $roomid,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'expire' => $expire, ];

        return $this->call('live.room.list', $params);
    }

    /**
     *  根据直播ID获取访客列表.
     *
     * @param string $liveid 直播ID
     * @param int    $page   页码
     * @param int    $size   每页个数
     *
     * @return array
     */
    public function liveVisitorList($liveid, $page = 1, $size = 10)
    {
        $params = [
            'liveid' => $liveid,
            'page' => $page,
            'size' => $size, ];

        return $this->call('live.visitor.list', $params);
    }

    /**
     *  根据直播ID，用户ID获取访客列表.
     *
     * @param string $liveid 直播ID
     * @param string $uid    用户ID
     *
     * @return array
     */
    public function liveVisitorGet($liveid, $uid)
    {
        $params = [
            'liveid' => $liveid,
            'uid' => $uid, ];

        return $this->call('live.visitor.get', $params);
    }

    /**
     *  根据直播ID获取提问列表.
     *
     * @param string $liveid 直播ID
     * @param int    $page   页码
     * @param int    $size   每页个数
     *
     * @return array
     */
    public function liveQuestionList($liveid, $page = 1, $size = 10)
    {
        $params = [
            'liveid' => $liveid,
            'page' => $page,
            'size' => $size, ];

        return $this->call('live.question.list', $params);
    }

    /**
     * 根据直播ID获取音频下载地址
     *
     * @param string $liveid 直播ID
     *
     * @return array
     */
    public function liveAudioDownloadUrl($liveid)
    {
        return $this->call('live.audio.download.url', ['liveid' => $liveid]);
    }

    /**
     *  根据直播ID获取回放访客列表.
     *
     * @param string $liveid 直播ID
     * @param int    $page   页码
     * @param int    $size   每页个数
     *
     * @return array
     */
    public function livePlaybackVisitorList($liveid, $page = 1, $size = 10)
    {
        $params = [
            'liveid' => $liveid,
            'page' => $page,
            'size' => $size, ];

        return $this->call('live.playback.visitor.list', $params);
    }

    /**
     *  按照时间区间获取回放访客列表    (时间区间不能大于7天).
     *
     * @param string $start_time 开始时间    格式：2016-01-01 00:00:00
     * @param string $end_time   结束时间    格式：2016-01-02 00:00:00
     * @param int    $page       页码
     * @param int    $size       每页个数
     *
     * @return array
     */
    public function livePlaybackVisitorTimeList($start_time, $end_time, $page = 1, $size = 10)
    {
        $params = [
            'start_time' => $start_time,
            'end_time' => $end_time,
            'page' => $page,
            'size' => $size, ];

        return $this->call('live.playback.visitor.timelist', $params);
    }

    /**
     * 获取直播PPT章节信息.
     *
     * @param int $liveid 直播ID
     *
     * @return array
     */
    public function liveChapterList($liveid)
    {
        $params = [
            'liveid' => $liveid, ];

        return $this->call('live.chapter.list', $params);
    }

    /**
     * 根据直播id获取回放视频.
     *
     * @param int $liveid 直播id
     *
     * @return array
     */
    public function livePlaybackVideo($liveid)
    {
        $params = [
            'liveid' => $liveid, ];

        return $this->call('live.playback.video', $params);
    }

    /**
     * 获取回放登录地址
     *
     * @param int $liveid 直播id
     *
     * @return array
     */
    public function livePlaybackLoginUrl($liveid)
    {
        $params = [
            'liveid' => $liveid, ];

        return $this->call('live.playback.loginUrl', $params);
    }

    /**
     *  按照直播ID获取投票列表.
     *
     * @param string $liveid 直播ID
     * @param int    $page   页码
     * @param int    $size   每页个数
     *
     * @return array
     */
    public function liveVoteList($liveid, $page = 1, $size = 10)
    {
        $params = [
            'liveid' => $liveid,
            'page' => $page,
            'size' => $size, ];

        return $this->call('live.vote.list', $params);
    }

    /**
     *  按照投票ID和直播ID获取投票详情.
     *
     * @param int $vid    投票ID
     * @param int $liveid 直播ID
     * @param int $page   页码
     * @param int $size   每页个数
     *
     * @return array
     */
    public function liveVoteDetail($vid, $liveid, $page = 1, $size = 10)
    {
        $params = [
            'vid' => $vid,
            'liveid' => $liveid,
            'page' => $page,
            'size' => $size, ];

        return $this->call('live.vote.detail', $params);
    }

    /**
     *  按照直播ID获取抽奖列表.
     *
     * @param string $liveid 直播ID
     * @param int    $page   页码
     * @param int    $size   每页个数
     *
     * @return array
     */
    public function liveLotteryList($liveid, $page = 1, $size = 10)
    {
        $params = [
            'liveid' => $liveid,
            'page' => $page,
            'size' => $size, ];

        return $this->call('live.lottery.list', $params);
    }

    /**
     *  按照直播ID获取私聊记录列表.
     *
     * @param string $liveid 直播ID
     * @param int    $page   页码
     * @param int    $size   每页个数
     *
     * @return array
     */
    public function livePrivateChatList($liveid, $page = 1, $size = 10)
    {
        $params = [
            'liveid' => $liveid,
            'page' => $page,
            'size' => $size, ];

        return $this->call('live.privateChat', $params);
    }

    /**
     *   增加一个直播课程.
     *
     * @param string $course_name  课程名称
     * @param string $account      发起直播课程的第三方主播账号
     * @param string $start_time   课程开始时间,格式: 2015-01-10 12:00:00
     * @param string $end_time     课程结束时间,格式: 2015-01-10 13:00:00
     * @param string $nickname     主播昵称
     * @param string $accountIntro 主播简介
     * @param array  $options      额外参数
     *
     * @return array
     */
    public function courseAdd($course_name, $account, $start_time, $end_time, $nickname = '', $accountIntro = '', $options = [])
    {
        $params = [
            'course_name' => $course_name,
            'account' => $account,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'nickname' => '' == $nickname ? $account : $nickname,
            'accountIntro' => $accountIntro,
            'options' => $options, ];

        return $this->call('course.add', $params);
    }

    /**
     *  进入一个课程直播.
     *
     * @param string $course_id 课程ID
     * @param string $uid       用户唯一ID
     * @param string $nickname  用户昵称
     * @param string $role      用户角色，枚举见:ROLE 定义
     * @param int    $expire    有效期,默认:3600(单位:秒)
     * @param array  $options   可选项，包括:gender:枚举见上面GENDER定义,avatar:头像地址,gid:用户分组
     *
     * @return array
     */
    public function courseAccess($course_id, $uid, $nickname, $role, $expire = 3600, $options = [])
    {
        $params = [
            'course_id' => $course_id,
            'uid' => $uid,
            'nickname' => $nickname,
            'role' => $role,
            //'roomid' => $roomid,
            'expire' => $expire,
            'options' => $options, ];

        return $this->call('course.access', $params);
    }

    /**
     *  进入一个课程回放.
     *
     * @param string $course_id 课程ID
     * @param string $uid       用户唯一ID
     * @param string $nickname  用户昵称
     * @param string $role      用户角色，枚举见:ROLE 定义
     * @param int    $expire    有效期,默认:3600(单位:秒)
     * @param array  $options   可选项，包括:gender:枚举见上面GENDER定义,avatar:头像地址
     *
     * @return array
     */
    public function courseAccessPlayback($course_id, $uid, $nickname, $role, $expire = 3600, $options = [])
    {
        $params = [
            'course_id' => $course_id,
            'uid' => $uid,
            'nickname' => $nickname,
            'role' => $role,
            //'roomid' => $roomid,
            'expire' => $expire,
            'options' => $options, ];

        return $this->call('course.access.playback', $params);
    }

    /**
     *  获取课程直播地址
     *
     * @param string $course_id 课程ID
     * @param string $uid       用户唯一ID
     * @param string $nickname  用户昵称
     * @param string $role      用户角色，枚举见:ROLE 定义
     * @param int    $expire    有效期,默认:3600(单位:秒)
     * @param array  $options   可选项，包括:gender:枚举见上面GENDER定义,avatar:头像地址,gid:用户分组
     *
     * @return string
     */
    public function courseAccessUrl($course_id, $uid, $nickname, $role, $expire = 3600, $options = [])
    {
        $accessAuth = $this->courseAccessKey($course_id, $uid, $nickname, $role, $expire, $options);

        return 'http://open.talk-fun.com/room.php?accessAuth='.$accessAuth;
    }

    /**
     *  获取验证key.
     *
     * @param string $course_id 课程ID
     * @param string $uid       用户唯一ID
     * @param string $nickname  用户昵称
     * @param string $role      用户角色，枚举见:ROLE 定义
     * @param int    $expire    有效期,默认:3600(单位:秒)
     * @param array  $options   可选项，包括:gender:枚举见上面GENDER定义,avatar:头像地址,gid:用户分组
     *
     * @return string
     */
    public function courseAccessKey($course_id, $uid, $nickname, $role, $expire = 3600, $options = [])
    {
        $params = [
            'openID' => trim($this->openID),
            'timestamp' => $_SERVER['REQUEST_TIME'],
            'course_id' => $course_id,
            'uid' => $uid,
            'nickname' => $nickname,
            'role' => $role,
            'expire' => $expire,
            'options' => json_encode($options), ];

        $params['sign'] = $this->generateSign($params);
        $accessAuth = $this->base64UrlEncode(json_encode($params));

        return $accessAuth;
    }

    /**
     *  获取回放地址
     *
     * @param string $course_id 课程ID
     * @param string $uid       用户唯一ID
     * @param string $nickname  用户昵称
     * @param string $role      用户角色，枚举见:ROLE 定义
     * @param int    $expire    有效期,默认:3600(单位:秒)
     * @param array  $options   可选项，包括:gender:枚举见上面GENDER定义,avatar:头像地址
     *
     * @return string
     */
    public function courseAccessPlaybackUrl($course_id, $uid, $nickname, $role, $expire = 3600, $options = [])
    {
        $accessAuth = $this->courseAccessKey($course_id, $uid, $nickname, $role, $expire, $options);

        return 'http://open.talk-fun.com/player.php?accessAuth='.$accessAuth;
    }

    /**
     *   查询课程信息.
     *
     * @param string $course_id 课程ID
     * @param int    $expire    有限期,默认86400
     * @param int    $options   额外参数
     *
     * @return array
     */
    public function courseGet($course_id, $expire = 86400, $options = 0)
    {
        $params = [
            'course_id' => $course_id,
            'expire' => $expire,
            'options' => $options, ];

        return $this->call('course.get', $params);
    }

    /**
     *  发送广播.
     *
     * @param string $course_id 课程ID
     * @param string $cmd       指令
     * @param array  $args      广播内容
     * @param array  $options   广播选项
     *
     * @return array
     */
    public function courseBroadcastSend($course_id, $cmd, $args, $options = [])
    {
        $params = [
            'course_id' => $course_id,
            'cmd' => $cmd,
            'params' => $args,
            'options' => $options, ];

        return $this->call('course.broadcast.send', $params);
    }

    /**
     *   删除课程.
     *
     * @param string $course_id 课程ID
     *
     * @return array
     */
    public function courseDelete($course_id)
    {
        $params = ['course_id' => $course_id];

        return $this->call('course.delete', $params);
    }

    /**
     *   课程列表(将返回开始时间在区间内的课程).
     *
     * @param string $start_time 开始时间区间,格式: 2015-01-01 12:00:00
     * @param string $end_time   结束时间区间,格式: 2015-01-02 12:00:00
     * @param int    $page       页码
     * @param int    $size       每页数量
     * @param array  $options    可选参数
     *
     * @return array
     */
    public function courseList($start_time, $end_time, $page = 1, $size = 10, $options = [])
    {
        $params = [
            'start_time' => $start_time,
            'end_time' => $end_time,
            'page' => $page,
            'size' => $size,
            'options' => $options, ];

        return $this->call('course.list', $params);
    }

    /**
     *   更新课程信息.
     *
     * @param string $course_id    课程ID
     * @param string $account      发起直播课程的第三方主播账号
     * @param string $course_name  课程名称
     * @param string $start_time   课程开始时间,格式:2015-01-01 12:00:00
     * @param string $end_time     课程结束时间,格式:2015-01-01 13:00:00
     * @param string $nickname     主播昵称
     * @param string $accountIntro 主播简介
     * @param array  $options      可选参数
     *
     * @return array
     */
    public function courseUpdate($course_id, $account, $course_name, $start_time, $end_time, $nickname = '', $accountIntro = '', $options = [])
    {
        $params = [
            'course_id' => $course_id,
            'course_name' => $course_name,
            'account' => $account,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'nickname' => '' == $nickname ? $account : $nickname,
            'accountIntro' => $accountIntro,
            'options' => $options, ];

        return $this->call('course.update', $params);
    }

    /**
     *  按照投票ID和课程ID获取投票详情.
     *
     * @param int $vid       投票ID
     * @param int $course_id 课程ID
     * @param int $page      页码
     * @param int $size      每页个数
     *
     * @return array
     */
    public function courseVoteDetail($vid, $course_id, $page = 1, $size = 10)
    {
        $params = [
            'vid' => $vid,
            'course_id' => $course_id,
            'page' => $page,
            'size' => $size, ];

        return $this->call('course.votes.detail', $params);
    }

    /**
     *  按照课程ID获取投票列表.
     *
     * @param string $course_id 课程ID
     * @param int    $page      页码
     * @param int    $size      每页个数
     * @param string $status
     *
     * @return array
     */
    public function courseVoteList($course_id, $page = 1, $size = 10, $status = 'normal')
    {
        $params = [
            'course_id' => $course_id,
            'page' => $page,
            'size' => $size,
            'status' => $status, ];

        return $this->call('course.votes', $params);
    }

    /**
     * 发布预发布的投票.
     *
     * @param int $vid       投票ID
     * @param int $course_id 课程ID
     *
     * @return array
     */
    public function courseVoteEmit($vid, $course_id)
    {
        $params = [
            'vid' => $vid,
            'course_id' => $course_id, ];

        return $this->call('course.votes.emit', $params);
    }

    /**
     * 删除投票.
     *
     * @param int $vid 投票ID
     *
     * @return array
     */
    public function courseVoteDelete($vid)
    {
        $params = [
            'vid' => $vid, ];

        return $this->call('course.votes.delete', $params);
    }

    /**
     * 更新投票.
     *
     * @param int   $vid     投票ID
     * @param array $options 要更新的信息
     *
     * @return array
     */
    public function courseVoteUpdate($vid, $options)
    {
        $params = [
            'vid' => $vid,
            'options' => $options, ];

        $files = [];
        if (isset($options['image']) && !empty($options['image'])) {
            if (file_exists($options['image'])) {
                if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
                    $files['image'] = new CURLFile($options['image']);
                } else {
                    $files['image'] = '@'.$options['image'];
                }
            } else {
                return [
                    'code' => self::CODE_FAIL,
                    'msg' => '文件'.$options['image'].'不存在', ];
            }
        }

        return $this->call('course.votes.update', $params, 'POST', $files);
    }

    /**
     *  按照课程ID获取抽奖列表.
     *
     * @param string $course_id 课程ID
     * @param int    $page      页码
     * @param int    $size      每页个数
     *
     * @return array
     */
    public function courseLotteryList($course_id, $page = 1, $size = 10)
    {
        $params = [
            'course_id' => $course_id,
            'page' => $page,
            'size' => $size, ];

        return $this->call('course.lottery.list', $params);
    }

    /**
     *  按照课程ID获取音频下载地址
     *
     * @param string $course_id 课程ID
     *
     * @return array
     */
    public function courseAudioDownloadUrl($course_id)
    {
        return $this->call('course.audio.download.url', ['course_id' => $course_id]);
    }

    /**
     *  根据课程ID获取访客列表.
     *
     * @param string $course_id 课程ID
     * @param int    $page      页码
     * @param int    $size      每页个数
     * @param array  $options   可选参数
     *
     * @return array
     */
    public function courseVisitorList($course_id, $page = 1, $size = 10, $options = [])
    {
        $params = [
            'course_id' => $course_id,
            'page' => $page,
            'size' => $size,
            'options' => $options, ];

        return $this->call('course.visitor.list', $params);
    }

    /**
     *  根据课程ID获取回放访客列表.
     *
     * @param string $course_id 课程ID
     * @param int    $page      页码
     * @param int    $size      每页个数
     * @param array  $options   可选参数
     *
     * @return array
     */
    public function coursePlaybackVisitorList($course_id, $page = 1, $size = 10, $options = [])
    {
        $params = [
            'course_id' => $course_id,
            'page' => $page,
            'size' => $size,
            'options' => $options, ];

        return $this->call('course.visitor.playback', $params);
    }

    /**
     *  根据时间获取访客列表.
     *
     * @param  string start_time   查询起始时间,格式:2015-01-01 12:00:00
     * @param  string end_time     查询结束时间,格式:2015-01-01 12:00:00
     * @param int   $page    页码
     * @param int   $size    每页个数
     * @param array $options
     *
     * @return array
     */
    public function courseVisitorListAll($start_time, $end_time, $page = 1, $size = 10, $options = [])
    {
        $params = [
            'start_time' => $start_time,
            'end_time' => $end_time,
            'page' => $page,
            'size' => $size,
            'options' => $options, ];

        return $this->call('course.visitor.listall', $params);
    }

    /**
     * 获取主播登录信息.
     *
     * @param string $account     主播账户
     * @param int    $accountType 主播账户类型
     * @param array  $options     其它可选项，ssl：是否使用https(true为使用，false为不使用)
     *
     * @return array
     */
    public function courseLogin($account, $accountType = self::ACCOUNT_TYPE_MT, $options = [])
    {
        $params['account'] = $account;
        $params['accountType'] = $accountType;
        $params['options'] = $options;

        return $this->call('course.login', $params);
    }

    /**
     * 获取课程PPT章节信息.
     *
     * @param int $course_id 课程ID
     *
     * @return array
     */
    public function courseChapterList($course_id)
    {
        $params = [
            'course_id' => $course_id, ];

        return $this->call('course.chapter.list', $params);
    }

    /**
     *  根据课程ID获取提问列表.
     *
     * @param string $course_id 课程ID
     * @param int    $page      页码
     * @param int    $size      每页个数
     *
     * @return array
     */
    public function courseQuestionList($course_id, $page = 1, $size = 10)
    {
        $params = [
            'course_id' => $course_id,
            'page' => $page,
            'size' => $size, ];

        return $this->call('course.question.list', $params);
    }

    /**
     *  获取课程鲜花记录.
     *
     * @param string $course_id 课程ID
     * @param int    $page      页码(默认:1)
     * @param int    $size      每页个数(默认:10)
     *
     * @return array
     */
    public function courseFlowerList($course_id, $page = 1, $size = 10)
    {
        $params = [
            'course_id' => $course_id,
            'page' => $page,
            'size' => $size, ];

        return $this->call('course.flower.list', $params);
    }

    /**
     *  获取课程聊天列表.
     *
     * @param string $course_id 课程id
     * @param int    $page      页码
     * @param int    $size      每页个数
     *
     * @return array
     */
    public function courseMessageList($course_id, $page = 1, $size = 20)
    {
        $params = [
            'course_id' => $course_id,
            'page' => $page,
            'size' => $size, ];

        return $this->call('course.message', $params);
    }

    /**
     * 课程课件上传.
     *
     * @param int   $course_id 欢拓课程ID
     * @param array $file      $file      文件信息["file"=>"文件路径","name"=>"file.doc"], 支持的课件格式为：ppt, pptx, doc, docx, pdf, jpg, jpeg, png, gif
     *
     * @return array|mixed
     *
     * @throws MTCloudException
     */
    public function courseDocumentUpload($course_id, array $file)
    {
        $params = [
            'course_id' => $course_id,
            'name' => $file['name'], ];

        $retval = $this->call('course.document.uploadurl.get', $params);
        if (0 === $retval['code'] && !empty($retval['data']['api'])) {
            $this->timeout = 3600;
            $params = [];
            $params[$retval['data']['field']] = version_compare(PHP_VERSION, '5.5.0') > 0 ? curl_file_create($file['file']) : '@'.$file['file'];
            $retval = $this->_request($retval['data']['api'], 'POST', $params);
        }

        return $retval;
    }

    /**
     *  获取课程课件列表.
     *
     * @param string $course_id 课程id
     * @param int    $page      页码
     *
     * @return array
     */
    public function courseDocumentList($course_id, $page = 1)
    {
        $params = [
            'course_id' => $course_id,
            'page' => $page, ];

        return $this->call('course.document', $params);
    }

    /**
     * 删除文档.
     *
     * @param int $id 文档ID
     *
     * @return array
     */
    public function courseDocumentDelete($id)
    {
        $params = [
            'id' => $id, ];

        return $this->call('document.delete', $params);
    }

    /**
     * 获取直播器启动协议参数.
     *
     * @param string $course_id 课程id
     *
     * @return array
     */
    public function courseLaunch($course_id)
    {
        $params = [
            'course_id' => $course_id, ];

        return $this->call('course.launch', $params);
    }

    /**
     * 根据课程ID获取回放视频.
     *
     * @param int   $course_id 课程id
     * @param array $options   可选参数
     *
     * @return array
     */
    public function courseVideo($course_id, $options = [])
    {
        $params = [
            'course_id' => $course_id,
            'options' => $options, ];

        return $this->call('course.video', $params);
    }

    /**
     * 根据课程ID获取课程配置.
     *
     * @param int $course_id 课程id
     *
     * @return array
     */
    public function courseConfig($course_id)
    {
        $params = [
            'course_id' => $course_id, ];

        return $this->call('course.getConfig', $params);
    }

    /**
     * 根据课程ID更新课程配置.
     *
     * @param int   $course_id 课程id
     * @param array $options   可选参数
     *
     * @return array
     */
    public function courseUpdateConfig($course_id, $options = [])
    {
        $params['course_id'] = $course_id;
        $params['options'] = $options;

        return $this->call('course.updateConfig', $params);
    }

    /**
     * 根据课程ID获取直播拉流地址
     *
     * @param int   $course_id 课程id
     * @param array $options
     *
     * @return array
     */
    public function courseStreamAddress($course_id, $options = [])
    {
        $params = [
            'course_id' => $course_id,
            'options' => $options, ];

        return $this->call('course.streamAddress', $params);
    }

    /**
     * 创建主播.
     *
     * @param string $account  接入方自已的主播唯一ID
     * @param string $nickname 主播昵称
     * @param string $intro    主播简介
     * @param string $password 主播密码
     *
     * @return array [description]
     */
    public function courseZhuboAdd($account, $nickname, $intro = '', $password = '')
    {
        $params['nickname'] = $nickname;
        $params['account'] = $account;
        $params['intro'] = $intro;
        $params['password'] = $password;

        return $this->call('course.zhubo.add', $params);
    }

    /**
     * 主播查询.
     *
     * @param int    $page    页码
     * @param int    $size    每页结果数量
     * @param string $account 接入方自己的主播唯一ID，非指定查询具体主播时不要填
     *
     * @return array [description]
     */
    public function courseZhuboList($page, $size, $account = '')
    {
        $params = [
            'page' => $page,
            'size' => $size,
            'account' => $account, ];

        return $this->call('course.zhubo.list', $params);
    }

    /**
     * 更新主播信息.
     *
     * @param string $account  接入方自己的主播唯一ID
     * @param string $nickname 主播昵称
     * @param string $intro    主播简介
     * @param string $password 主播密码
     *
     * @return array [description]
     */
    public function courseZhuboUpdate($account, $nickname, $intro = '', $password = '')
    {
        $params['account'] = $account;
        $params['nickname'] = $nickname;
        $params['intro'] = $intro;
        $params['password'] = $password;

        return $this->call('course.zhubo.update', $params);
    }

    /**
     * 主播关系绑定.
     *
     * @param string $account 接入方自己的主播唯一ID
     * @param int    $id      欢拓主播id
     *
     * @return array [description]
     */
    public function courseZhuboBindAccount($account, $id)
    {
        $params['account'] = $account;
        $params['id'] = $id;

        return $this->call('course.zhubo.bindAccount', $params);
    }

    /**
     *  修改主播头像.
     *
     * @param string $account  接入方自己的主播唯一ID
     * @param string $filename 图片路径(支持图片格式:jpg、jpeg)
     *
     * @return array
     *
     * @throws MTCloudException
     */
    public function courseZhuboPortrait($account, $filename)
    {
        $params = ['account' => $account];
        $ret = $this->call('course.zhubo.portrait', $params);

        if (self::CODE_SUCCESS === $ret['code']) {
            $filename = realpath($filename);
            if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
                $params[$ret['data']['field']] = new CURLFile($filename);
            } else {
                $params[$ret['data']['field']] = '@'.$filename;
            }

            return $this->_request($ret['data']['api'], 'POST', $params);
        }

        return [
            'code' => self::CODE_FAIL,
            'msg' => '该主播不存在', ];
    }

    /**
     * 虚拟用户导入.
     *
     * @param int   $course_id 课程ID
     * @param array $userList  虚拟用户列表
     * @param int   $total     虚拟用户总数
     *
     * @return array
     */
    public function courseRobotSet($course_id, $userList = [], $total = 0)
    {
        $params = [
            'course_id' => $course_id,
            'userList' => $userList,
            'total' => $total, ];

        return $this->call('course.robot.set', $params, 'POST');
    }

    /**
     * 滚动公告接口.
     *
     * @param string $course_id 课程ID
     * @param string $content   滚动公告内容
     * @param string $link      滚动公告链接
     * @param string $duration  滚动通知显示时长(单位：秒)
     *
     * @return array
     */
    public function courseNoticeRoll($course_id, $content, $link, $duration)
    {
        $params = [
            'course_id' => $course_id,
            'content' => $content,
            'link' => $link,
            'duration' => $duration, ];

        return $this->call('course.notice.roll', $params);
    }

    /**
     * 发起投票.
     *
     * @param string $course_id 课程ID
     * @param string $uid       投票发布者，合作方用户ID
     * @param string    nickname        投票发布者，合作方用户昵称
     * @param string $title    投票主题
     * @param string $label    投票标签
     * @param string $op       选项
     * @param string $type     类型，0为单选，1为多选
     * @param string $optional 若为单选则传1，多选则传的值为多少表示可以选几项
     * @param string $answer   答案，设置第几项为答案，传入 "0" 表示第一个选项为正确答案，传入 "0,2" 表示第一和第三项为正确答案，不设置答案则传空字符串
     * @param string $image    本地图片路径
     * @param array  $options  可选参数
     *
     * @return array
     */
    public function courseVoteAdd($course_id, $uid, $nickname, $title, $label, $op, $type, $optional, $answer = '', $image = '', $options = [])
    {
        $params = [
            'course_id' => $course_id,
            'uid' => $uid,
            'nickname' => $nickname,
            'title' => $title,
            'label' => $label,
            'op' => $op,
            'type' => $type,
            'optional' => $optional,
            'answer' => $answer,
            'options' => $options, ];

        $files = [];
        if (!empty($image)) {
            if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
                $files['image'] = new CURLFile($image);
            } else {
                $files['image'] = '@'.$image;
            }
        }

        return $this->call('course.votes.add', $params, 'POST', $files);
    }

    /**
     * @param int    $vid        投票ID
     * @param int    $showResult 是否显示投票结果
     * @param string $uid        投票结束者，合作方用户ID
     * @param string $nickname   投票结束者，合作方用户昵称
     *
     * @return array
     */
    public function courseVoteEnd($vid, $showResult, $uid, $nickname)
    {
        $params = [
            'vid' => $vid,
            'showResult' => $showResult,
            'uid' => $uid,
            'nickname' => $nickname, ];

        return $this->call('course.votes.end', $params);
    }

    /**
     * 获取在线用户列表.
     *
     * @param string $course_id  课程ID
     * @param string $start_time 查询开始时间,格式:2015-01-01 12:00:00
     * @param string $end_time   查询结束时间,格式:2015-01-01 13:00:00
     * @param int    $page       页码
     * @param int    $size       每页数量
     *
     * @return array
     */
    public function courseOnlineList($course_id, $start_time, $end_time, $page = 1, $size = 10)
    {
        $params = [
            'course_id' => $course_id,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'page' => $page,
            'size' => $size, ];

        return $this->call('course.online.list', $params);
    }

    /**
     * 获取在线管理员列表.
     *
     * @param string $start_time 查询开始时间,格式:2015-01-01 12:00:00
     * @param string $end_time   查询结束时间,格式:2015-01-01 13:00:00
     * @param int    $page       页码
     * @param int    $size       每页数量
     *
     * @return array
     */
    public function courseOnlineAdmin($start_time, $end_time, $page = 1, $size = 10)
    {
        $params = [
            'start_time' => $start_time,
            'end_time' => $end_time,
            'page' => $page,
            'size' => $size, ];

        return $this->call('course.online.admin', $params);
    }

    /**
     * 发起提问.
     *
     * @param int    $course_id 课程ID
     * @param string $content   提问内容
     * @param string $uid       用户ID
     * @param string $role      用户角色
     * @param string $nickname  用户昵称
     * @param array  $options   可选参数
     *
     * @return array
     */
    public function courseQaAdd($course_id, $content, $uid, $role, $nickname, $options = [])
    {
        $params = [
            'course_id' => $course_id,
            'content' => $content,
            'uid' => $uid,
            'role' => $role,
            'nickname' => $nickname,
            'options' => $options, ];

        return $this->call('course.qa.add', $params);
    }

    /**
     * 审核通过提问.
     *
     * @param int $qid       提问ID
     * @param int $course_id 课程ID
     *
     * @return array
     */
    public function courseQaAudit($qid, $course_id)
    {
        $params = [
            'qid' => $qid,
            'course_id' => $course_id, ];

        return $this->call('course.qa.audit', $params);
    }

    /**
     * 删除提问.
     *
     * @param int $qid       提问ID
     * @param int $course_id 课程ID
     *
     * @return array
     */
    public function courseQaDelete($qid, $course_id)
    {
        $params = [
            'qid' => $qid,
            'course_id' => $course_id, ];

        return $this->call('course.qa.delete', $params);
    }

    /**
     * 回复提问.
     *
     * @param int    $qid       提问ID
     * @param int    $course_id 课程ID
     * @param string $content   提问内容
     * @param string $uid       用户ID
     * @param string $nickname  用户昵称
     * @param array  $options   可选参数
     *
     * @return array
     */
    public function courseQaAnswer($qid, $course_id, $content, $uid, $nickname, $options = [])
    {
        $params = [
            'qid' => $qid,
            'course_id' => $course_id,
            'content' => $content,
            'uid' => $uid,
            'nickname' => $nickname,
            'options' => $options, ];

        return $this->call('course.qa.answer', $params);
    }

    /**
     * 获取问答列表.
     *
     * @param int   $course_id 课程ID
     * @param array $options   可选参数
     *
     * @return array
     */
    public function courseQaList($course_id, $options = [])
    {
        $params = [
            'course_id' => $course_id,
            'options' => $options, ];

        return $this->call('course.qa.list', $params);
    }

    /**
     * 获取提问总数.
     *
     * @param string $course_id 课程ID
     *
     * @return array
     */
    public function courseQaTotal($course_id)
    {
        $params = [
            'course_id' => $course_id, ];

        return $this->call('course.qa.total', $params);
    }

    /**
     *  按照课程ID获取私聊记录列表.
     *
     * @param string $course_id 课程ID
     * @param int    $page      页码
     * @param int    $size      每页个数
     *
     * @return array
     */
    public function coursePrivateChatList($course_id, $page = 1, $size = 10)
    {
        $params = [
            'course_id' => $course_id,
            'page' => $page,
            'size' => $size, ];

        return $this->call('course.privateChat', $params);
    }

    //add online list

    /**
     *  添加剪辑.
     *
     * @param int    $liveid    直播ID
     * @param string $name      剪辑名称
     * @param array  $time      剪辑时间，array(array('start'=>60,'end'=>180))
     * @param string $isRelated 是否关联源直播，默认不关联
     *
     * @return array
     */
    public function clipAdd($liveid, $name, $time, $isRelated = '')
    {
        $params = [
            'liveid' => $liveid,
            'name' => $name,
            'time' => $time,
            'isRelated' => $isRelated, ];

        return $this->call('clip.add', $params);
    }

    /**
     *  修改剪辑.
     *
     * @param int    $clipid    剪辑ID
     * @param string $name      剪辑名称
     * @param array  $time      剪辑时间，array(array('start'=>60,'end'=>180))
     * @param string $isRelated 是否关联源直播，默认不关联
     *
     * @return array
     */
    public function clipUpdate($clipid, $name, $time, $isRelated = '')
    {
        $params = [
            'clipid' => $clipid,
            'name' => $name,
            'time' => $time,
            'isRelated' => $isRelated, ];

        return $this->call('clip.update', $params);
    }

    /**
     *  删除剪辑.
     *
     * @param int $clipid 剪辑ID
     *
     * @return array
     */
    public function clipDelete($clipid)
    {
        $params = [
            'clipid' => $clipid, ];

        return $this->call('clip.delete', $params);
    }

    /**
     *  获取剪辑信息.
     *
     * @param int $clipid 剪辑ID
     *
     * @return array
     */
    public function clipGet($clipid)
    {
        $params = [
            'clipid' => $clipid, ];

        return $this->call('clip.get', $params);
    }

    /**
     *  获取剪辑列表.
     *
     * @param int    $page   页码
     * @param int    $size   条数
     * @param string $liveid 直播ID
     *
     * @return array
     */
    public function clipList($page = 1, $size = 10, $liveid = '')
    {
        $params = [
            'page' => $page,
            'size' => $size,
            'liveid' => $liveid, ];

        return $this->call('clip.list', $params);
    }

    /**
     *  根据课程id获取剪辑列表.
     *
     * @param int $course_id 课程id
     * @param int $page      页码
     * @param int $size      条数
     *
     * @return array
     */
    public function clipListByCid($course_id, $page = 1, $size = 10)
    {
        $params = [
            'course_id' => $course_id,
            'page' => $page,
            'size' => $size, ];

        return $this->call('clip.course.list', $params);
    }

    /**
     *  添加剪辑.
     *
     * @param int    $course_id 课程ID
     * @param string $name      剪辑名称
     * @param array  $time      剪辑时间，array(array('start'=>60,'end'=>180))
     * @param string $isRelated 是否关联源直播，默认不关联
     *
     * @return array
     */
    public function clipAddByCid($course_id, $name, $time, $isRelated = '')
    {
        $params = [
            'course_id' => $course_id,
            'name' => $name,
            'time' => $time,
            'isRelated' => $isRelated, ];

        return $this->call('clip.course.add', $params);
    }

    /**
     * 课件上传.
     *
     * @param int   $roomid 房间ID
     * @param array $file   $file   文件信息["file"=>"文件路径","name"=>"file.doc"], 支持的课件格式为：ppt, pptx, doc, docx, pdf, jpg, jpeg, png, gif
     *
     * @return array|mixed
     *
     * @throws MTCloudException
     */
    public function documentUpload($roomid, array $file)
    {
        $params = [
            'roomid' => $roomid,
            'name' => $file['name'], ];

        $retval = $this->call('document.uploadurl.get', $params);
        if (0 === $retval['code'] && !empty($retval['data']['api'])) {
            $this->timeout = 3600;
            $params = [];
            $params[$retval['data']['field']] = version_compare(PHP_VERSION, '5.5.0') > 0 ? curl_file_create($file['file']) : '@'.$file['file'];
            $retval = $this->_request($retval['data']['api'], 'POST', $params);
        }

        return $retval;
    }

    /**
     * 课件下载地址
     *
     * @param int $id 开放平台的文档ID
     *
     * @return array
     */
    public function documentDownload($id)
    {
        $params = [
            'id' => $id, ];

        return $this->call('document.downloadurl.get', $params);
    }

    /**
     * 课件列表.
     *
     * @param int $roomid 根据房间id获取课件列表
     *
     * @return array
     */
    public function documentList($roomid)
    {
        $params = ['roomid' => $roomid];

        return $this->call('document.list', $params);
    }

    /**
     * 根据课件id获取课件详细信息.
     *
     * @param int $id 课件ID
     *
     * @return array
     */
    public function documentGet($id)
    {
        $params = ['id' => $id];

        return $this->call('document.get', $params);
    }

    /**
     * 根据课件id删除课件.
     *
     * @param int $id 课件ID
     *
     * @return array
     */
    public function documentDelete($id)
    {
        $params = ['id' => $id];

        return $this->call('document.delete', $params);
    }

    /**
     * 创建部门.
     *
     * @param string $departmentName 部门名称
     *
     * @return array
     */
    public function departmentCreate($departmentName)
    {
        $params = ['departmentName' => $departmentName];

        return $this->call('department.create', $params);
    }

    /**
     * 更新部门信息.
     *
     * @param int    $departmentId   部门id
     * @param string $departmentName 部门名称
     *
     * @return array
     */
    public function departmentUpdate($departmentId, $departmentName)
    {
        $params = [
            'departmentId' => $departmentId,
            'departmentName' => $departmentName, ];

        return $this->call('department.update', $params);
    }

    /**
     * 删除部门.
     *
     * @param int $departmentId 部门id
     *
     * @return array
     */
    public function departmentDelete($departmentId)
    {
        $params = ['departmentId' => $departmentId];

        return $this->call('department.delete', $params);
    }

    /**
     * 获取部门信息.
     *
     * @param int $departmentId 部门id
     *
     * @return array
     */
    public function departmentGet($departmentId)
    {
        $params = ['departmentId' => $departmentId];

        return $this->call('department.get', $params);
    }

    /**
     * 批量获取部门信息.
     *
     * @param array $departmentIds 部门id数组
     *
     * @return array
     */
    public function departmentGetBatch($departmentIds = [])
    {
        $params = ['departmentIds' => $departmentIds];

        return $this->call('department.getBatch', $params);
    }

    /**
     * 获取视频上传地址
     *
     * @param string $account     主播账号
     * @param        $accountType
     * @param        $title
     * @param        $md5
     * @param array  $options
     *
     * @return array
     */
    public function videoGetUploadUrl($account, $accountType, $title, $md5, $options = [])
    {
        $params = [
            'account' => $account,
            'accountType' => $accountType,
            'title' => $title,
            'md5' => $md5,
            'options' => $options, ];

        return $this->call('video.getUploadUrl', $params);
    }

    /**
     * 获取视频信息.
     *
     * @param int $videoId 视频ID
     * @param int $expire  视频有效期(单位：秒)
     *
     * @return array
     */
    public function videoGet($videoId, $expire = 3600)
    {
        $params = [
            'videoId' => $videoId,
            'expire' => $expire, ];

        return $this->call('video.get', $params);
    }

    /**
     * 批量获取视频信息.
     *
     * @param int $videoIds 视频ID
     * @param int $expire   视频有效期(单位：秒)
     *
     * @return array
     */
    public function videoGetBatch($videoIds, $expire = 3600)
    {
        $params = [
            'videoIds' => $videoIds,
            'expire' => $expire, ];

        return $this->call('video.getBatch', $params);
    }

    /**
     * @param int $videoId 视频ID
     *
     * @return array
     */
    public function videoDelete($videoId)
    {
        $params = ['videoId' => $videoId];

        return $this->call('video.delete', $params);
    }

    /**
     * @param string $fileName      要上传的本地路径文件
     * @param string $account       主播帐号
     * @param int    $accountType   帐号类型
     * @param string $title         视频标题
     * @param string $nickname      主播昵称
     * @param string $accountIntro  主播简介
     * @param bool   $segmentUpload 是否分段上传，true为使用分段上传，false为不使用
     * @param array  $course        创建视频课程的课程参数
     *
     * @return array|bool|mixed|string
     *
     * @throws MTCloudException
     */
    public function videoUpload($fileName, $account, $accountType, $title, $nickname = '', $accountIntro = '', $segmentUpload = true, $course = [])
    {
        //$retval = $fileMd5 = '';

        if (file_exists($fileName)) {
            $fileMd5 = md5_file($fileName);
        } else {
            return [
                'code' => self::CODE_FAIL,
                'msg' => '文件不存在', ];
        }

        $options = [
            'nickname' => $nickname,
            'accountIntro' => $accountIntro,
            'course' => $course, ];
        $retval = $this->videoGetUploadUrl($account, $accountType, $title, $fileMd5, $options);

        if (isset($retval['code']) && self::CODE_SUCCESS === $retval['code']) {
            $fileName = realpath($fileName);

            if (true === $segmentUpload) {
                $uploadUrl = $retval['data']['resumeUploadUrl'];
                $chunkListUrl = $retval['data']['chunkListUrl'];

                $cutFileSize = 1048576;
                $fileCount = ceil(filesize($fileName) / $cutFileSize);

                $fp = fopen($fileName, 'rb');

                // 获取上传过的分片
                $chunkList = [];
                $chunkListRes = $this->_request($chunkListUrl);
                if (isset($chunkListRes['data']) && !empty($chunkListRes['data'])) {
                    $chunkList = $chunkListRes['data'];
                }

                $chunk = 0;

                // 分段上传
                while ($content = fread($fp, $cutFileSize)) {
                    ++$chunk;

                    if (in_array($chunk, $chunkList)) {
                        continue;
                    }

                    $postData = [
                        'chunk' => $chunk,
                        'chunks' => $fileCount,
                        'md5' => $fileMd5,
                        'chunkMd5' => md5($content), ];

                    $fileDatas = [
                        'filedata' => [
                            'fileName' => basename($fileName),
                            'contentType' => 'application/octet-stream',
                            'content' => $content, ], ];

                    $this->timeout = 3600;
                    $retval = $this->uploadFileData($uploadUrl, $postData, $fileDatas);
                }

                fclose($fp);
            } else {
                $uploadUrl = $retval['data']['uploadUrl'];

                $params = [];

                if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
                    $params[$retval['data']['field']] = new CURLFile($fileName);
                } else {
                    $params[$retval['data']['field']] = '@'.$fileName;
                }

                $this->timeout = 3600;

                return $this->_request($uploadUrl, 'POST', $params);
            }
        } elseif (isset($retval['code']) && self::CODE_VIDEO_UPLOADED === $retval['code']) {
            $retval['code'] = self::CODE_SUCCESS;

            return $retval;
        }

        return $retval;
    }

    /**
     * 获取音频上传地址
     *
     * @param string $account     主播账号
     * @param int    $accountType 账号类型
     * @param string $md5         音频文件md5
     * @param array  $options
     *
     * @return array
     */
    public function audioGetUploadUrl($account, $accountType, $md5, $options = [])
    {
        $params = [
            'account' => $account,
            'accountType' => $accountType,
            'md5' => $md5,
            'options' => $options, ];

        return $this->call('audio.getUploadUrl', $params);
    }

    /**
     * 获取音频信息.
     *
     * @param int $audioId 音频ID
     * @param int $expire  音频有效期(单位：秒)
     *
     * @return array
     */
    public function audioGet($audioId, $expire = 3600)
    {
        $params = [
            'audioId' => $audioId,
            'expire' => $expire, ];

        return $this->call('audio.get', $params);
    }

    /**
     * 批量获取音频信息.
     *
     * @param int $audioIds 音频ID
     * @param int $expire   音频有效期(单位：秒)
     *
     * @return array
     */
    public function audioGetBatch($audioIds, $expire = 3600)
    {
        $params = [
            'audioIds' => $audioIds,
            'expire' => $expire, ];

        return $this->call('audio.getBatch', $params);
    }

    /**
     * @param int $audioId 音频ID
     *
     * @return array
     */
    public function audioDelete($audioId)
    {
        $params = ['audioId' => $audioId];

        return $this->call('audio.delete', $params);
    }

    /**
     * 模块设置.
     *
     * @param array $options 可选参数
     *
     * @return array
     */
    public function moduleSet($options)
    {
        $files = [];

        $fileField = [
            'livePcLogo',
            'playbackPcLogo',
            'clientLogo', ];
        foreach ($fileField as $_field) {
            if (isset($options[$_field]) && !empty($options[$_field])) {
                if (file_exists($options[$_field])) {
                    if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
                        $files[$_field] = new CURLFile($options[$_field]);
                    } else {
                        $files[$_field] = '@'.$options[$_field];
                    }
                } else {
                    return [
                        'code' => self::CODE_FAIL,
                        'msg' => '文件'.$options[$_field].'不存在', ];
                }

                unset($options[$_field]);
            }
        }

        $params = ['options' => $options];

        return $this->call('module.set', $params, 'POST', $files);
    }

    /**
     * 发评论.
     *
     * @param int    $assetId   产品ID
     * @param int    $assetType 产品类型
     * @param string $uid       用户ID
     * @param string $nickname  用户昵称
     * @param string $comment   评论内容
     * @param string $ip        用户IP
     * @param int    $replyId   回复ID
     *
     * @return array
     */
    public function commentAdd($assetId, $assetType, $uid, $nickname, $comment, $ip, $replyId = 0)
    {
        $params = [
            'assetId' => $assetId,
            'assetType' => $assetType,
            'uid' => $uid,
            'nickname' => $nickname,
            'comment' => $comment,
            'ip' => $ip,
            'replyId' => $replyId, ];

        return $this->call('comment.add', $params);
    }

    /**
     * 删除评论.
     *
     * @param int $assetId   产品ID
     * @param int $assetType 产品类型
     * @param int $id        评论ID
     *
     * @return array
     */
    public function commentDelete($assetId, $assetType, $id)
    {
        $params = [
            'assetId' => $assetId,
            'assetType' => $assetType,
            'id' => $id, ];

        return $this->call('comment.delete', $params);
    }

    /**
     * 获取评论列表.
     *
     * @param int $assetId   产品ID
     * @param int $assetType 产品类型
     * @param int $page      页码
     *
     * @return array
     */
    public function commentList($assetId, $assetType, $page = 1)
    {
        $params = [
            'assetId' => $assetId,
            'assetType' => $assetType,
            'page' => $page, ];

        return $this->call('comment.list', $params);
    }

    /**
     * 直接上传文件内容.
     *
     * @param string $url       上传地址
     * @param array  $postData  参数
     * @param array  $fileDatas 文件信息
     *
     * @return bool|mixed|string
     */
    public function uploadFileData($url, $postData = [], $fileDatas = [])
    {
        $eol = "\r\n";
        $data = '';

        $mime_boundary = md5($_SERVER['REQUEST_TIME']);

        if (!empty($postData)) {
            foreach ($postData as $_key => $_value) {
                $data .= '--'.$mime_boundary.$eol;
                $data .= 'Content-Disposition: form-data; name="'.$_key.'"'.$eol.$eol;
                $data .= $_value.$eol;
                $data .= '--'.$mime_boundary.$eol;
            }
        }

        foreach ($fileDatas as $_key => $_value) {
            $fileName = $_value['fileName'];
            $contentType = $_value['contentType'];
            $content = $_value['content'];

            $data .= '--'.$mime_boundary.$eol;
            $data .= 'Content-Disposition: form-data; name="'.$_key.'"; filename="'.$fileName.'"'.$eol;
            $data .= 'Content-Type: '.$contentType.$eol.$eol;
            $data .= $content.$eol;
            $data .= '--'.$mime_boundary.'--'.$eol.$eol; // finish with two eol's!!
        }

        $params = [
            'http' => [
                'timeout' => $this->timeout,
                'method' => 'POST',
                'header' => 'Content-Type: multipart/form-data; boundary='.$mime_boundary.$eol,
                'content' => $data, ], ];

        $ctx = stream_context_create($params);
        $response = @file_get_contents($url, FILE_TEXT, $ctx);

        $retval = json_decode($response, true);
        if (!is_array($retval)) {
            $retval = $response;
        }

        return $retval;
    }

    /**
     *   生成一個短地址
     *
     * @param string $url url地址
     *
     * @return array
     */
    public function utilsShortUrl($url)
    {
        $params = ['url' => $url];

        return $this->call('utils.shorturl', $params);
    }

    /**
     *   获取课程峰值
     *
     * @param int $course_id 课程ID
     *
     * @return array
     */
    public function statsPeakCourse($course_id)
    {
        $params = ['course_id' => $course_id];

        return $this->call('stats.peak.course', $params);
    }

    /**
     *   获取直播峰值
     *
     * @param int $liveid 直播ID
     *
     * @return array
     */
    public function statsPeakLive($liveid)
    {
        $params = ['liveid' => $liveid];

        return $this->call('stats.peak.live', $params);
    }

    /**
     * 自动登录到欢拓console后台.
     *
     * @param int    $uid    欢拓后台管理员id
     * @param int    $expire 自动登录地址的过期时间
     * @param string $target 登录成功后跳转的目标，形式：类型-ID
     *                       如跳到课程ID为"123456"的页面：course-123456
     *
     * @return string $url 生成的自动登录地址
     */
    public function consoleAutoLogin($uid, $expire = 3600, $target = '')
    {
        $expire = $_SERVER['REQUEST_TIME'] + $expire;
        $params = [
            'id' => $uid,
            'openID' => $this->openID,
            'expire' => $expire, ];
        $sign = $this->generateSign($params);
        $url = 'http://console.talk-fun.com/?autologin='.$uid.'-'.$sign.'-'.$expire;
        if ($target) {
            $url .= '&target='.urlencode($target);
        }

        return $url;
    }

    //==================================================
    //              回调逻辑
    //==================================================

    /**
     *   注册回调函数
     *   示例：  http://www.example.com/callback.php
     *   class ExampleHandler {
     *        public function handler($cmd,$params){
     *            if($cmd === 'user.login'){
     *                //do something...
     *                $response = array('code'=>MTCloud::CODE_SUCCESS,'data'=>array('uid'=>'abcde@qq.com','nickname'=>'昵称','role'=>MTCloud::ROLE_ADMIN));
     *            }
     *            return $response;
     *        }
     *   }.
     *
     *   $MTCloud = new MTCloud();
     *   $MTCloud->registerCallbackHandler(array(new ExampleHandler,'handler'));
     *   $MTCloud->callbackService();
     *
     * @param array $callbackHandler
     */
    public function registerCallbackHandler($callbackHandler = [])
    {
        $this->callbackHandler = $callbackHandler;
    }

    /**
     *   回调参数验证、处理，及响应.
     *
     * @throws MTCloudException
     */
    public function callbackService()
    {
        if (!$this->callbackHandler) {
            throw new MTCloudException('回调 handler 未设置!');
        }

        $sysParams = $_POST;

        if (!isset($sysParams['sign']) || $this->generateSign($sysParams) !== $sysParams['sign']) {
            self::response(['code' => self::CODE_SIGN_ERROR]);
        }

        if ($_SERVER['REQUEST_TIME'] - $sysParams['timestamp'] > 7200) {
            self::response([]);
        }

        $retval = call_user_func_array($this->callbackHandler, [
            $sysParams['cmd'],
            json_decode($sysParams['params'], true), ]);

        self::response($retval);
    }

    public function response($retval)
    {
        exit(json_encode($retval));
    }

    //==================================================
    //              以下为系统逻辑
    //==================================================

    /**
     *   构造欢拓云sign.
     *
     * @param array  $params 业务参数
     * @param string $salt   加密salt
     *
     * @return string 生成的秘钥
     */
    public function generateSign($params, $salt = '')
    {
        unset($params['sign']);
        ksort($params);
        $secret_key = $salt ? md5(substr(trim($this->openToken), 6, 16).$salt) : $this->openToken;
        $keyStr = '';
        foreach ($params as $key => $value) {
            $keyStr .= $key.$value;
        }
        $keyStr .= $secret_key;
        $sign = md5($keyStr);

        return $sign;
    }

    /**
     *   调用欢拓API.
     *
     * @param string $cmd        调用的API名称
     * @param array  $params     API参数
     * @param string $httpMethod
     * @param array  $files
     *
     * @return array|mixed
     */
    public function call($cmd, $params = [], $httpMethod = 'GET', $files = [])
    {
        $sysParams = [];
        $sysParams['openID'] = trim($this->openID);
        $sysParams['timestamp'] = $_SERVER['REQUEST_TIME'];
        $sysParams['cmd'] = $cmd;
        $sysParams['params'] = urlencode(json_encode($params));
        $sysParams['ver'] = $this->version;
        $sysParams['format'] = $this->format;

        $sysParams['sign'] = $this->generateSign($sysParams);
        if ('GET' == $httpMethod) {
            $this->requestParam = [
                'url' => $this->restUrl.'?'.http_build_query($sysParams),
                'method' => $httpMethod,
                'data' => [], ];
        } else {
            if (!empty($files)) {
                $sysParams = array_merge($sysParams, $files);
            }

            $this->requestParam = [
                'url' => $this->restUrl,
                'method' => $httpMethod,
                'data' => $sysParams, ];
        }

        try {
            $retval = $this->_request($this->requestParam['url'], $this->requestParam['method'], $this->requestParam['data']);
        } catch (MTCloudException $e) {
            $retval['code'] = -100;
        }

        if (-100 == $retval['code'] && 0 === strpos($this->requestParam['url'], $this->restUrl)) {
            $this->requestParam['url'] = str_replace($this->restUrl, $this->restUrl2, $this->requestParam['url']);
            $this->restUrl = $this->restUrl2;
            try {
                $retval = $this->_request($this->requestParam['url'], $this->requestParam['method'], $this->requestParam['data']);
            } catch (MTCloudException $e) {
                return $e;
            }
        }

        return $retval;
    }

    /**
     * @param        $url
     * @param string $method
     * @param array  $data
     *
     * @return array|mixed
     *
     * @throws MTCloudException
     */
    private function _request($url, $method = 'POST', $data = [])
    {
        $options = [
            CURLOPT_HTTPHEADER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => [
                'Expect:', ],
            CURLOPT_USERAGENT => 'MT-PHP-SDK', ];

        if ('POST' == $method) {
            $options[CURLOPT_POST] = 1;

            if (is_array($data)) {
                $postMultipart = false;
                foreach ($data as $_key => $_val) {
                    if ((is_object($_val) && $_val instanceof CURLFile) || (is_string($_val) && '@' === substr($_val, 0, 1))) {
                        $postMultipart = true;
                    }
                }

                $options[CURLOPT_POSTFIELDS] = $postMultipart ? $data : http_build_query($data);
            } else {
                $options[CURLOPT_POSTFIELDS] = $data;
            }
        }

        $curl = curl_init($url);
        curl_setopt_array($curl, $options);

        $page = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (200 === $statusCode) {
            if ('xml' === $this->format) {
                $retval = $page;
            } else {
                $retval = json_decode($page, true);
                if (!is_array($retval)) {
                    throw new MTCloudException('返回的数据错误！'.$page);
                }
            }

            return $retval;
        }

        return [
                'code' => -100,
                'msg' => 'CURL ERROR: no:'.curl_errno($curl).',msg:'.curl_error($curl),
                'statusCode' => $statusCode, ];
    }

    /**
     *   生成一个游客ID.
     *
     * @return string
     */
    public function generateGuestId()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        }
        mt_srand((float) microtime() * 10000); //optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid = substr($charid, 0, 8).$hyphen.substr($charid, 8, 4).$hyphen.substr($charid, 12, 4).$hyphen.substr($charid, 16, 4).$hyphen.substr($charid, 20, 12);

        return $uuid;
    }

    public function base64UrlEncode($plainText)
    {
        $base64 = base64_encode($plainText);
        $base64 = str_replace('=', '', $base64);
        $base64url = strtr($base64, '+/', '-_');

        return $base64url;
    }
}
