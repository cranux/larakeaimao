<?php


namespace Cranux\Larakeaimao;


use Cranux\Larakeaimao\Exceptions\HttpException;
use Cranux\Larakeaimao\Services\GuzzleHttp;

class YaAiHttp
{
    /**
     * @var int 事件类型<br><br> 100=> 私聊消息<br>200=> 群聊消息<br>300=> 暂无<br>400=> 群成员增加<br>410=> 群成员减少<br>500=> 收到好友请求<br>600=> 二维码收款<br>700=> 收到转账<br>800=> 软件开始启动<br>900=> 新的账号登录完成<br>910=> 账号下线
     */
    public $type;
    /**
     * @var string 1级来源id（群消息事件下，1级来源为群id，2级来源为发消息的成员id，私聊事件下都一样）
     */
    public $from_wxid;
    /**
     * @var string 1级来源昵称（比如发消息的人昵称）<br>
     */
    public $from_name;
    /**
     * @var string 2级来源id（群消息事件下，1级来源为群id，2级来源为发消息的成员id，私聊事件下都一样）
     */
    public $final_from_wxid;
    /**
     * @var string 2级来源昵称
     */
    public $final_from_name;
    /**
     * @var string 当前登录的账号（机器人）标识id
     */
    public $robot_wxid;
    /**
     * @var string 消息内容
     */
    public $msg;
    /**
     * @var int 消息类型（请务必使用新版http插件）<br><br> 1 =>文本消息 <br>3 => 图片消息 <br>34 => 语音消息 <br>42 => 名片消息 <br>43 =>视频 <br>47 => 动态表情 <br> 48 =>地理位置<br>49 => 分享链接 <br>2001 => 红包<br>2002 => 小程序<br>2003 => 群邀请 <br><br>更多请参考sdk模块常量值
     * ）
     */
    public $msg_type;
    /**
     * @var string 如果是文件消息（图片、语音、视频、动态表情），这里则是可直接访问的网络地址，非文件消息时为空
     */
    public $file_url;
    /**
     * @var int 请求时间(时间戳10位版本)
     */
    public $time;
    /**
     * @var string 机器猫接口地址
     */
    public $baseUri = "http://127.0.0.1:6060/";
    /**
     * @var mixed|string
     */
    public $sRequUrl = "post";

    /**
     * @var array
     */
    public $config = [];

    public function __construct($config = null)
    {
        if (!empty($config)) {//未配置，使用默认配置
            $this->baseUri = $config['yAbaseUri'];
            $this->sRequUrl = $config['yAsRequUrl'];
            $this->config = $config;
        }
    }

    /**
     * 解析回调消息
     * @return array
     */
    public function parseWechat()
    {
        $data = $_POST;
        $responseData['type'] = $this->type = $data['leixing'] ?? '';
        $responseData['from_wxid'] = $this->from_wxid = $data['from_wxid'] ?? '';
        $responseData['from_name'] = $this->from_name = urldecode($data['from_name'] ?? '');
        $responseData['final_from_wxid'] = $this->final_from_wxid = $data['final_from_wxid']  ?? '';
        $responseData['final_from_name'] = $this->final_from_name = urldecode($data['final_from_name']  ?? '');
        $responseData['robot_wxid'] = $this->robot_wxid = $data['robot_wxid']  ?? '';
        $responseData['msg'] = $this->msg = urldecode($data['msg'] ?? '');
        $responseData['msg_type'] = $this->msg_type = intval($data['type'] ?? '');
        $responseData['file_url'] = $this->file_url = $data['file_url'] ?? '';
        $responseData['msg'] = $this->msg = urldecode($data['msg'] ?? '');
        $responseData['time'] = $this->time = $data['time']  ?? '';
        return $responseData;
    }

    /**
     * 发送文字消息(好友或者群)
     * @access public
     * @param $msg 消息内容
     * @param null $robwxid 登录账号id，用哪个账号去发送这条消息
     * @param null $to_wxid 对方的id，可以是群或者好友id
     * @return mixed|string
     * @throws HttpException
     */
    public function sendTextMsg($msg, $robwxid=null, $to_wxid=null){
        $data = array();
        $data['type'] = 100;             // Api数值（可以参考 - api列表demo）
        $data['msg']  = rawurlencode($msg); // 发送内容
        $data['to_wxid'] = $to_wxid ?: $this->from_wxid;     // 对方id
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;  // 账户id，用哪个账号去发送这条消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     * 名片消息
     * @access public
     * @param  string $card_data     名片格式数据
     * @param  null $robwxid 登录账号id，用哪个账号去发送这条消息
     * @param  null $to_wxid 对方的id，可以是群或者好友id
     * @return mixed|string
     * @throws HttpException
     */
    public function businessCardMsg($card_data, $robwxid=null, $to_wxid=null){
        $data = array();
        $data['type'] = 42;             // Api数值（可以参考 - api列表demo）
        $data['to_wxid'] = $to_wxid ?: $this->from_wxid;     // 对方id （支持好友/群ID）
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;  // 账户id，用哪个账号去发送这条消息
        $data['card_data']  = $card_data; // 名片格式数据
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     * 发送群消息并艾特某人
     *
     * @param  string $from_wxid 群id
     * @param  string $final_from_wxid 艾特的id，群成员的id ,发送消息用户的ID
     * @param  string $final_from_name 艾特的昵称，群成员的昵称,发送消息用户名
     * @param  string $msg     消息内容
     * @param  null $robwxid 账户id，用哪个账号去发送这条消息
     * @return mixed|string
     * @throws HttpException
     */
    public function sendGroupAtMsg($from_wxid, $final_from_wxid, $final_from_name, $msg,$robwxid=null){
        $data = array();
        $data['type'] = 102;             // Api数值（可以参考 - api列表demo）
        $data['msg']  = rawurlencode($msg); // 消息内容
        $data['group_wxid'] = $from_wxid;     // 群id
        $data['member_wxid'] = $final_from_wxid;     // 艾特的id，群成员的id
        $data['member_name'] = $final_from_name;     // 艾特的昵称，群成员的昵称
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;  // 账户id，用哪个账号去发送这条消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     * 发送图片消息
     * @access public
     * @param  string $path    图片的绝对路径 如下：
    1、发送网络图片,需要编码如rawurlencode('https://www.5devip.com/zb_users/theme/ccvok_zsy/image/logo1.png')
    2、发送本地图片，需要编码如rawurlencode('E:\Program Files\图片\1.png')
     * @param  null $robwxid 登录账号id，用哪个账号去发送这条消息
     * @param  null $to_wxid 对方的id，可以是群或者好友id
     * @return mixed
     */
    public function sendImageMsg($path, $robwxid=null, $to_wxid=null){
        $data = array();
        $data['type'] = 103;             // Api数值（可以参考 - api列表demo）
        $data['msg']  = rawurlencode($path);           // 发送的图片的绝对路径
        $data['to_wxid'] = $to_wxid ?: $this->from_wxid;     // 对方id
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;  // 账户id，用哪个账号去发送这条消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     * 发送视频消息
     * @access public
     * @param  string $path    视频的绝对路径 （参考图片）
     * @param  null $robwxid 账户id，用哪个账号去发送这条消息
     * @param  null $to_wxid 对方的id，可以是群或者好友id
     * @return mixed|string
     * @throws HttpException
     */
    public function sendVideoMsg($path, $robwxid=null, $to_wxid=null){
        $data = array();
        $data['type'] = 104;             // Api数值（可以参考 - api列表demo）
        $data['msg']  = rawurlencode($path);          // 发送的视频的绝对路径
        $data['to_wxid'] = $to_wxid ?: $this->from_wxid;     // 对方id
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;  // 账户id，用哪个账号去发送这条消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     * 发送文件消息
     * @access public
     * @param  string $path    文件的绝对路径 （参考图片）
     * @param  null $robwxid 账户id，用哪个账号去发送这条消息
     * @param  null $to_wxid 对方的id，可以是群或者好友id
     * @return mixed|string
     * @throws HttpException
     */
    public function sendFileMsg($path, $robwxid=null, $to_wxid=null){
        $data = array();
        $data['type'] = 105;             // Api数值（可以参考 - api列表demo）
        $data['msg']  = rawurlencode($path);          // 发送的文件的绝对路径
        $data['to_wxid'] = $to_wxid ?: $this->from_wxid;     // 对方id（默认发送至来源的id，也可以发给其他人）
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;  // 账户id，用哪个账号去发送这条消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     * 发送动态表情
     * @access public
     * @param  string $path    动态表情文件（通常是gif）的绝对路径 （发送图片消息一样）
    1、发送网络图片,需要编码如rawurlencode('https://www.5devip.com/logo1.gif')
    2、发送动态表情文件,需要编码如rawurlencode('http://emoji.qpic.cn/wx_emoji/QpbHpO8M3AxldnGM8OgZSibX3vEAGwJDtiaEh6ceX2g0PFrOJJA1wibLVtIOa5m5ZJu/')
    3、发送本地图片，需要编码如rawurlencode('E:\Program Files\图片\1.gif')
     * @param  string $robwxid 账户id，用哪个账号去发送这条消息
     * @param  string $to_wxid 对方的id，可以是群或者好友id
     * @return mixed|string
     * @throws HttpException
     */
    public function sendEmojiMsg($path, $robwxid = null, $to_wxid = null){
        $data = array();
        $data['type'] = 106;             // Api数值（可以参考 - api列表demo）
        $data['msg']  = rawurlencode($path);          // 发送的动态表情的绝对路径
        $data['to_wxid'] = $to_wxid ?: $this->from_wxid;     // 对方id（默认发送至来源的id，也可以发给其他人）
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;  // 账户id，用哪个账号去发送这条消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     * 发送分享链接
     * @access public
     * @param  string $title      链接标题
     * @param  string $text       链接内容
     * @param  string $target_url 跳转链接 允许空参数
     * @param  string $pic_url    图片链接 允许空参数
     * @param  string $icon_url;  图标的链接 允收空参数
     * @param  null $robwxid    账户id，用哪个账号去发送这条消息
     * @param  null $to_wxid    对方的id，可以是群或者好友id
     * @return mixed|string
     * @throws HttpException
     */
    public function sendLinkMsg($title, $text, $target_url, $pic_url,$icon_url, $robwxid = null, $to_wxid = null){
        // 封装链接结构体
        $data = array();
        $data['type'] = 107;
        $data['title'] = $title;
        $data['text']  = $text;
        $data['target_url']   = $target_url;
        $data['pic_url']   = $pic_url;
        $data['icon_url']  = $icon_url;   // 图标的链接
        $data['to_wxid'] = $to_wxid ?: $this->from_wxid;     // 对方id（默认发送至来源的id，也可以发给其他人）
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;  // 账户id，用哪个账号去发送这条消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     * 发送音乐分享
     * @access public
     * @param  string $name    歌曲名字
     * @param  null $robwxid 账户id，用哪个账号去发送这条消息
     * @param  null $to_wxid 对方的id，可以是群或者好友id
     * @return mixed|string
     * @throws HttpException
     */
    public function sendMusicMsg($name, $robwxid = null, $to_wxid = null){
        $data = array();
        $data['type'] = 108;             // Api数值（可以参考 - api列表demo）
        $data['msg']  = $name;           // 歌曲名字
        $data['to_wxid'] = $to_wxid ?: $this->from_wxid;     // 对方id（默认发送至来源的id，也可以发给其他人）
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;  // 账户id，用哪个账号去发送这条消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     * 取指定登录账号的昵称
     * @access public
     * @param  null $robwxid 账户id
     * @return mixed|string
     * @throws HttpException
     */
    public function getRobotName($robwxid = null){
        $data = array();
        $data['type'] = 201;             // Api数值（可以参考 - api列表demo）
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;  // 账户id
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     * 取指定登录账号的头像	(限测版 失败)
     * @access public
     * @param  null $robwxid 账户id
     * @return mixed|string 头像http地址
     * @throws HttpException
     */
    public function getRobotHeadImageUrl($robwxid = null){
        $data = array();
        $data['type'] = 202;             // Api数值（可以参考 - api列表demo）
        $data['robot_wxid'] = $robwxid ?: $this->robot_wxid;  // 账户id
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     * 取登录账号列表
     * @access public
     * @return string 当前框架已登录的账号信息列表
    可以获取到名称，图片，微信号等
     * @throws HttpException
     */
    public function getLoggedAccountList(){
        $data = array();
        $data['type'] = 203;             // Api数值（可以参考 - api列表demo）
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     * 取好友列表(只能获取到数量)
     * @access public
     * @param  string $robwxid    账户id
     * @param  string $is_refresh 是否刷新
     * @return mixed|string 当前框架已登录的账号信息列表
     * @throws HttpException
     */
    public function getFriendList($robwxid='', $is_refresh=0){
        $data = array();
        $data['type'] = 204;                // Api数值（可以参考 - api列表demo）
        $data['robot_wxid'] = $robwxid;     // 账户id（可选，如果填空字符串，即取所有登录账号的好友列表，反正取指定账号的列表）
        $data['is_refresh'] = $is_refresh;  // 是否刷新列表，0 从缓存获取 / 1 刷新并获取
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     * 取群聊列表 (只能获取到群数量)
     * @access public
     * @param  string $robwxid    账户id
     * @param  string $is_refresh 是否刷新
     * @return mixed|string 当前框架已登录的账号信息列表
     * @throws HttpException
     */
    public function getGroupList($robwxid='', $is_refresh=0){
        $data = array();
        $data['type'] = 205;                // Api数值（可以参考 - api列表demo）
        $data['robot_wxid'] = $robwxid;     // 账户id（可选，如果填空字符串，即取所有登录账号的好友列表，反正取指定账号的列表）
        $data['is_refresh'] = $is_refresh;  // 是否刷新列表，0 从缓存获取 / 1 刷新并获取
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     *取群成员列表，获取群有多少个人(只能获取到群数量)
     * @access public
     * @param  string $robwxid    账户id
     * @param  string $group_wxid 群id
     * @param  string $is_refresh 是否刷新
     * @return mixed|string 当前框架已登录的账号信息列表
     * @throws HttpException
     */
    public function getGroupMemberList($robwxid, $group_wxid, $is_refresh=0){
        $data = array();
        $data['type'] = 206;                // Api数值（可以参考 - api列表demo）
        $data['robot_wxid'] = $robwxid;     // 账户id
        $data['group_wxid'] = $group_wxid;  // 群id
        $data['is_refresh'] = $is_refresh;  // 是否刷新列表，0 从缓存获取 / 1 刷新并获取
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     *取群成员资料（限测版 失败）
     * @access public
     * @param  string $robwxid     账户id
     * @param  string $group_wxid  群id
     * @param  string $member_wxid 群成员id
     * @return mixed|string json_string
     * @throws HttpException
     */
    public function getGroupMember($robwxid, $group_wxid, $member_wxid){
        $data = array();
        $data['type'] = 207;                  // Api数值（可以参考 - api列表demo）
        $data['robot_wxid'] = $robwxid;       // 账户id，取哪个账号的资料
        $data['group_wxid'] = $group_wxid;    // 群id
        $data['member_wxid'] = $member_wxid;  // 群成员id
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     * 接收好友转账 (未测试)
     * @access public
     * @param  string $robwxid     账户id
     * @param  string $friend_wxid 朋友id
     * @param  string $json_string 转账事件原消息
     * @return mixed|string json_string
     * @throws HttpException
     */
    public function acceptTransfer($robwxid = null, $friend_wxid = null, $json_string = null){
        $data = array();
        $data['type'] = 301;                  // Api数值（可以参考 - api列表demo）
        $data['robot_wxid']  = $robwxid ?: $this->robot_wxid;      // 账户id
        $data['friend_wxid'] = $friend_wxid ?: $this->from_wxid;  // 朋友id
        $data['msg']  = $json_string ?: $this->msg;         // 转账事件原消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     *同意群聊邀请 (未测试 限测版)
     * @access public
     * @param  string $robwxid     账户id
     * @param  string $json_string 同步消息事件中群聊邀请原消息
     * @return mixed|string json_string
     * @throws HttpException
     */
    public function agreeGroupInvite($robwxid = null, $json_string = null){
        $data = array();
        $data['type'] = 302;                  // Api数值（可以参考 - api列表demo）
        $data['robot_wxid']  = $robwxid ?: $this->robot_wxid;      // 账户id
        $data['msg']  = $json_string ?: $this->msg;         // 同步消息事件中群聊邀请原消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }

    /**
     * 同意好友请求 (未测试)
     * @access public
     * @param  string $robwxid     账户id
     * @param  string $json_string 好友请求事件中原消息
     * @return mixed|string json_string
     * @throws HttpException
     */
    public function agreeFriendVerify($robwxid = null, $json_string = null){
        $data = array();
        $data['type'] = 303;                  // Api数值（可以参考 - api列表demo）
        $data['robot_wxid']  = $robwxid ?: $this->robot_wxid;      // 账户id
        $data['msg']  = $json_string ?: $this->msg;         // 好友请求事件中原消息
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }
    /**
     * 修改好友备注
     * @access public
     * @param  string $robwxid     账户id
     * @param  string $friend_wxid 好友id
     * @param  string $note 新备注（空字符串则是删除备注）
     * @return mixed|string json_string
     */
    public function setFriendNote($robwxid, $friend_wxid, $note){
        $data = array();
        $data['type'] = 304;                  // Api数值（可以参考 - api列表demo）
        $data['robot_wxid']  = $robwxid;      // 账户id
        $data['friend_wxid'] = $friend_wxid;  // 朋友id
        $data['msg']  = $note;               // 新备注（空字符串则是删除备注）
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }
    /**
     * 删除好友 (未测试 限测版)
     * @access public
     * @param  string $robwxid     账户id
     * @param  string $friend_wxid 好友id
     * @return mixed|string json_string
     */
    public function deleteFriend($robwxid, $friend_wxid){
        $data = array();
        $data['type'] = 305;                  // Api数值（可以参考 - api列表demo）
        $data['robot_wxid']  = $robwxid;      // 账户id
        $data['friend_wxid'] = $friend_wxid;  // 朋友id
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }
    /**
     * 踢出群成员 (未测试)
     * @access public
     * @param  string $robwxid     账户id
     * @param  string $group_wxid  群id
     * @param  string $member_wxid 群成员id
     * @return mixed|string json_string
     */
    public function removeGroupMember($member_wxid, $group_wxid = null, $robwxid = null){
        $data = array();
        $data['type'] = 306;                  // Api数值（可以参考 - api列表demo）
        $data['robot_wxid']  = $robwxid ?: $this->robot_wxid;      // 账户id
        $data['group_wxid']  = $group_wxid ?: $this->from_wxid;  // 群id
        $data['member_wxid'] = $member_wxid;  // 群成员id
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }
    /**
     * 修改群名称
     * @access public
     * @param  string $robwxid     账户id
     * @param  string $group_wxid  群id
     * @param  string $group_name  新群名
     * @return mixed|string json_string
     */
    public function setGroupName($robwxid, $group_wxid, $group_name){
        $data = array();
        $data['type'] = 307;                  // Api数值（可以参考 - api列表demo）
        $data['robot_wxid']  = $robwxid;      // 账户id
        $data['group_wxid']  = $group_wxid;  // 群id
        $data['group_name']  = $group_name;   // 新群名
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }
    /**
     * 修改群公告
     * @access public
     * @param  string $robwxid     账户id
     * @param  string $group_wxid  群id
     * @param  string $content      新公告
     * @return mixed|string json_string
     */
    public function setGroupNotice($robwxid, $group_wxid, $content){
        $data = array();
        $data['type'] = 308;                  // Api数值（可以参考 - api列表demo）
        $data['robot_wxid']  = $robwxid;      // 账户id
        $data['group_wxid']  = $group_wxid;  // 群id
        $data['content']      = $content;       // 新公告
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }
    /**
     * 建立新群 (未测试 限测版)
     * @access public
     * @param  string $robwxid     账户id
     * @param  array  $friends     三个人及以上的好友id数组，['wxid_1xxx', 'wxid_2xxx', 'wxid_3xxx', 'wxid_4xxx']
     * @return mixed|string json_string
     */
    public function creatGroup($robwxid,array $friends){
        $data = array();
        $data['type'] = 309;              // Api数值（可以参考 - api列表demo）
        $data['robot_wxid']  = $robwxid;  // 账户id
        $data['friends']     = $friends;  // 好友id数组
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }
    /**
     * 退出群聊	(限测版 失败)
     * @access public
     * @param  string $robwxid     账户id
     * @param  string $group_wxid  群id
     * @return mixed|string json_string
     */
    public function quitGroup($robwxid, $group_wxid){
        $data = array();
        $data['type'] = 310;                // Api数值（可以参考 - api列表demo）
        $data['robot_wxid']  = $robwxid;    // 账户id
        $data['group_wxid']  = $group_wxid; // 群id
        $response = array('data' => json_encode($data));
        return $this->sendRequest($response, 'POST');
    }
    /**
     * 邀请加入群聊
     * @access public
     * @param  string $robwxid     账户id
     * @param  string $group_wxid  群id
     * @param  string $friend_wxid 要邀请的好友ID
     * @return mixed|string json_string
     */
    public function inviteInGroup($robwxid, $group_wxid, $friend_wxid){
        $data = array();
        $data['type'] = 311;                  // Api数值（可以参考 - api列表demo）
        $data['robot_wxid']   = $robwxid;     // 账户id
        $data['group_wxid']   = $group_wxid;  // 群id
        $data['friend_wxid']  = $friend_wxid; // 要邀请的好友ID
        $response = array('data' => json_encode($data));
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