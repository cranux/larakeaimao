<h1 align="center"> larakeaimao </h1>

<p align="center"> sdk.</p>


## Installing

```shell
$ composer require cranux/larakeaimao
```

## Usage

#### **代码示例**

1. **接收消息回掉**

   ```php
   /接收消息回掉
   $lovelyCat = Factory::LovelyCat([
       'baseUri'=>'https://wm.pyvue.com/',
       'sRequUrl'=>'api/send',
   ]);
   // 解析本次消息 并将消息复制给类属性
   $parseWechat = $lovelyCat->parseWechat();
   // 回复本次消息
   return $lovelyCat->sendTextMsg('111');
   ```

2. #### 主动发送消息 

   ```php
   $lovelyCat = Factory::LovelyCat([
               'baseUri'=>'https://wm.pyvue.com/',
               'sRequUrl'=>'api/send',
           ]);
   // 两种设置参数的方法
   // 第一如下
   $lovelyCat->from_wxid = '11232434';
   $lovelyCat->robot_wxid = 'wxaa11';
   // 或者 在方法中直接传入
   return $lovelyCat->sendTextMsg('111','wxaa11','11232434');
   ```

**接口列表：**

发送文本消息              send_text_msg()

发送群消息并@某人      send_group_at_msg()

发送图片消息              send_image_msg()

发送视频消息              send_video_msg()

发送文件消息              send_file_msg()

发送动态表情              send_emoji_msg()

发送分享链接              send_link_msg()

发送音乐消息              send_music_msg()

取指定登录账号的昵称      get_robot_name()

取指定登录账号的头像      get_robot_headimgurl()

取登录账号列表            get_logged_account_list()

取好友列表                get_friend_list()

取群聊列表                get_group_list()

取群成员资料              get_group_member()

取群成员列表              get_group_member_list()

接收好友转账              accept_transfer()

同意群聊邀请              agree_group_invite()

同意好友请求              agree_friend_verify()

修改好友备注              modify_friend_note()

删除好友                  delete_friend()

踢出群成员                remove_group_member()

修改群名称                modify_group_name()

修改群公告                modify_group_notice()

建立新群                  building_group()

退出群聊                  quit_group()

邀请加入群聊              invite_in_group()
邀请测试      

## License

MIT
