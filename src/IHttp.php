<?php

namespace Cranux\Larakeaimao;

use Cranux\Larakeaimao\Exceptions\HttpException;
use Cranux\Larakeaimao\Services\GuzzleHttp;

class IHttp
{
    private $host;
    private $port;
    private $authorization;
    /**
     * @var string 事件名称 EventLogin':新的账号登录成功/下线时 EventGroupMsg':群消息事件 EventFriendMsg私聊消息事件 EventReceivedTransfer':收到转账事件 EventScanCashMoney':面对面收款（二维码收款时）EventFriendVerify':好友请求事件（插件3.0版本及以上）EventContactsChange':朋友变动事件（插件4.0版本及以上，当前为测试版，还未启用，留以备用）EventGroupMemberAdd':群成员增加事件（新人进群） EventGroupMemberDecrease
     */
    public $event;
    /**
     * @var string 机器人id
     */
    public $robot_wxid;
    /**
     * @var string 机器人昵称 一般空值
     */
    public $robot_name;
    /**
     * @var int 消息类型 1/文本消息 3/图片消息 34/语音消息  42/名片消息  43/视频 47/动态表情 48/地理位置  49/分享链接  2000/转账 2001/红包  2002/小程序  2003/群邀请
     */
    public $type;
    /**
     * @var string 来源群id
     */
    public $from_wxid;
    /**
     * @var string 来源群名称
     */
    public $from_name;
    /**
     * @var string 具体发消息的群成员id/私聊时用户id
     */
    public $final_from_wxid;
    /**
     * @var string 具体发消息的群成员昵称/私聊时用户昵称
     */
    public $final_from_name;
    /**
     * @var string 发给谁，往往是机器人自己(也可能别的成员收到消息)
     */
    public $to_wxid;
    /**
     * @var string 金额，只有"EventReceivedTransfer"事件才有该参数
     */
    public $money;
    /**
     * @var string
     */
    public $msgid;
    /**
     * @var  string 消息体(str/json) 不同事件和不同type都不一样，自己去试验吧
     */
    public $msg;
    /**
     * @var array
     */
    public $config = [];



    /**
     * @param $config
     */
    public function __construct($config = null)
    {
        if (!empty($config)) {//未配置，使用默认配置
            $this->baseUri = $config['iHttpUri'];
            $this->sRequUrl = $config['iHttpUrl'];
            $this->config = $config;
        }
    }
    /**
     * 解析回调消息
     * @return array
     */
    public function parseWechat($data)
    {
        $responseData['event'] = $this->event = $data['event'] ?? '';
        $responseData['robot_wxid'] = $this->robot_wxid = $data['robot_wxid'] ?? '';
        $responseData['robot_name'] = $this->robot_name = $data['robot_name'] ?? '';
        $responseData['type'] = $this->type = $data['type'] ?? '';
        $responseData['from_wxid'] = $this->from_wxid = $data['from_wxid'] ?? '';
        $responseData['from_name'] = $this->from_name = $data['from_name'] ?? '';
        $responseData['final_from_wxid'] = $this->final_from_wxid = $data['final_from_wxid'] ?? '';
        $responseData['final_from_name'] = $this->final_from_name = $data['final_from_name'] ?? '';
        $responseData['to_wxid'] = $this->to_wxid = $data['to_wxid'] ?? '';
        $responseData['money'] = $this->money = $data['money'] ?? '';
        $responseData['msg'] = $this->msg = $data['msg'] ?? ($data['content']??'');
        return $responseData;
    }

    /**
     * // https://doc.vwzx.com/web/#/6?page_id=123
     *  param
     * >>>  event 事件名称
     * >>>  robot_wxid 机器人id
     * >>>  group_wxid 群id
     * >>>  member_wxid 群艾特人id
     * >>>  member_name 群艾特人昵称
     * >>>  to_wxid 接收方(群/好友)
     * >>>  msg 消息体(str/json)
     * param.event
     * >>> SendTextMsg 发送文本消息 robot_wxid to_wxid(群/好友) msg
     * >>> 下面的几个文件类型的消息path为服务器里的路径如"D:/a.jpg"，会优先使用，文件不存在则使用 url(网络地址)
     * >>> SendImageMsg 发送图片消息 robot_wxid to_wxid(群/好友) msg(name[md5值或其他唯一的名字，包含扩展名例如1.jpg], url,patch)
     * >>> SendVideoMsg 发送视频消息 robot_wxid to_wxid(群/好友) msg(name[md5值或其他唯一的名字，包含扩展名例如1.mp4], url,patch)
     * >>> SendFileMsg 发送文件消息 robot_wxid to_wxid(群/好友) msg(name[md5值或其他唯一的名字，包含扩展名例如1.txt], url,patch)
     * >>> SendEmojiMsg 发送动态表情 robot_wxid to_wxid(群/好友) msg(name[md5值或其他唯一的名字，包含扩展名例如1.gif], url,patch)
     * >>> SendGroupMsgAndAt 发送群消息并艾特(4.4只能艾特一人) robot_wxid, group_wxid, member_wxid, member_name, msg
     * >>> SendLinkMsg 发送分享链接 robot_wxid, to_wxid(群/好友), msg(title, text, target_url, pic_url, icon_url)
     * >>> SendMusicMsg 发送音乐分享 robot_wxid, to_wxid(群/好友), msg(music_name, type=0)
     * >>> SendCardMsg 发送名片消息(被禁用) robot_wxid to_wxid(群/好友) msg(微信号)
     * >>> SendMiniApp 发送小程序 robot_wxid to_wxid(群/好友) msg(小程序消息的xml内容)
     * >>> GetRobotName 取登录账号昵称 robot_wxid
     * >>> GetRobotHeadimgurl 取登录账号头像 robot_wxid
     * >>> GetLoggedAccountList 取登录账号列表 不需要参数
     * >>> GetFriendList 取好友列表 robot_wxid msg(is_refresh,out_rawdata)//是否更新缓存 是否原始数据
     * >>> GetGroupList 取群聊列表 robot_wxid(不传返回全部机器人的)，msg(is_refresh)
     * >>> GetGroupMemberList 取群成员列表 robot_wxid, group_wxid msg(is_refresh)
     * >>> GetGroupMemberInfo 取群成员详细 robot_wxid, group_wxid, member_wxid msg(is_refresh)
     * >>> AcceptTransfer 接收好友转账 robot_wxid, to_wxid, msg
     * >>> AgreeGroupInvite 同意群聊邀请 robot_wxid, msg
     * >>> AgreeFriendVerify 同意好友请求 robot_wxid, msg
     * >>> EditFriendNote 修改好友备注 robot_wxid, to_wxid, msg
     * >>> DeleteFriend 删除好友 robot_wxid, to_wxid
     * >>> GetAppInfo 取插件信息 无参数
     * >>> GetAppDir 取应用目录 无
     * >>> AddAppLogs 添加日志 msg
     * >>> ReloadApp 重载插件 无
     * >>> RemoveGroupMember 踢出群成员 robot_wxid, group_wxid, member_wxid
     * >>> EditGroupName 修改群名称 robot_wxid, group_wxid, msg
     * >>> EditGroupNotice 修改群公告 robot_wxid, group_wxid, msg
     * >>> BuildNewGroup 建立新群 robot_wxid, msg(好友Id用"|"分割)
     * >>> QuitGroup 退出群聊 robot_wxid, group_wxid
     * >>> InviteInGroup 邀请加入群聊 robot_wxid, group_wxid, to_wxid
     * @param $event
     * @param $msg
     * @param $group_wxid
     * @param $member_wxid
     * @param $member_name
     * @return false|string
     */
    public function sendMsg($event='SendTextMsg',$msg,$group_wxid = '',$member_wxid = '',$member_name = '')
    {
        $data = [
            'success' => true,
            "message" => "successful!",
            "event" => $event,
            "robot_wxid" => $this->robot_wxid,
            "to_wxid" => $this->from_wxid ?: $this->final_from_wxid,
            "member_wxid" => $member_name,
            "member_name" => $member_wxid,
            "group_wxid" => $group_wxid,
            "msg" => $msg,
        ];
        $response = json_encode($data);
        return $response;
    }

    /**
     *
     * @param $event
     * @param $msg
     * @param $group_wxid
     * @param $member_wxid
     * @param $member_name
     * @return mixed|string
     * @throws HttpException
     */
    public function AsyncSendMsg($event,$msg,$group_wxid = '',$member_wxid = '',$member_name = '')
    {
        $data = [
            "event" => "SendMusicMsg",
            "robot_wxid" => $this->robot_wxid,
            "to_wxid" => $this->from_wxid ?: $this->final_from_wxid,
            "member_wxid" => $member_name,
            "member_name" => $member_wxid,
            "group_wxid" => $group_wxid,
            "msg" => $msg,
        ];
        $response = json_encode($data);
        return $this->sendRequest($response, 'POST');
    }
    /**
     * @param mixed $params 表单参数
     * @param int $timeout 超时时间
     * @param string $method 请求方法 POST / GET
     * @return mixed|string
     * @throws HttpException
     */
    public function sendRequest($params, $method = 'GET', $timeout = 10)
    {
        try {
            $res = (new GuzzleHttp())->sendRequest($this->baseUri, $method, $this->sRequUrl, $params, $timeout);
            if (!empty($res)) {
                $data = json_decode($res['data'], true);
                if ($data) {
                    $res = $data;
                } else {
                    $res = urldecode($res['data']);
                }
            }
            return $res;
        }catch (\Exception $e){
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

}