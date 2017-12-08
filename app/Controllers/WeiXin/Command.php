<?php
/**
 * User: yongli
 * Date: 17/9/19
 * Time: 11:17
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace App\Controllers\WeiXin;

use App\Controllers\Base;
use Api\MemberModel;
use YP_Date;

class Command extends Base
{
    /**
     *
     */
    public function commandPost()
    {
        $this->responseMsg();
    }

    /**
     * 消息认证
     */
    public function singCheckGet()
    {
        $weiCode = $this->request->getGet('wei_code');
        error_log('weiCode=' . $weiCode);
        if ($weiCode != null) {
            $echoStr = $this->request->getGet('echoStr');
            if ($this->checkSignature($weiCode)) {
                echo $echoStr;
                exit;
            }
        }
        exit;
    }

    /**
     * 回复信息
     */
    public function responseMsg()
    {
        // 获得xml格式的字符串
        $postStr = $GLOBALS ["HTTP_RAW_POST_DATA"];
        // 解释XML字符串为一个对象
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        // 信息的发送方
        $fromUsername = $postObj->FromUserName;
        //这个是微信后台中原始ID,可以用来做wei_code,信息的接收方
        $weiCode = $toUsername = $postObj->ToUserName;
        // 加载当前要发送信息给哪家商户的信息
        $shop = $this->loadShopInfo($weiCode);
        // 获得信息类型
        $MsgType = $postObj->MsgType;
        if ($MsgType == 'event') {
            // 使用第三方接口
            $three = $this->sendThreePlatform($shop, $postStr);
            // 获得事件类型
            $Event = $postObj->Event;
            //订阅，这里有可能是重复订阅，要注意
            switch ($Event) {
                case 'subscribe':
                    // 转发到第三方认证
                    // $a = $this->getAuth ( $shop ['authmode'] );
                    // if ($a == false) {//如果关闭了微信认证，则直接转发到第三方
                    // 	if (! empty ( $three )) {
                    // 		echo $three;
                    // 	}
                    // 	exit ();
                    // }
                    // // 关注的商家数据库中不存在
                    // if (empty ( $shop )) {
                    // 	$result_error = '商家已经取消微信关注上网功能';
                    // 	// 将错误信息发送出去
                    // 	$this->sendMsg ( $fromUsername, $toUsername, $result_error );
                    // } else {
                    // 	//检测是否设置通过微信认认证
                    // 	$a = $this->getAuth ( $shop ['authmode'] );
                    // 	if ($a == false) {
                    // 		echo $three;
                    // 		exit;
                    // 	}
                    // }
                    // // 获得商家的名字
                    // $shopname = $shop ['shopname'];
                    // // 获取微信自动认证地址
                    // $url = $this->getTokenUrl ( $shop, $fromUsername );
                    // // 认证通过显示的首条信息
                    // $contentStr = "欢迎光临 " . $shopname . "，上网请直接点击：<a target=\"_blank\"  href=\"$url\">我要上网</a>,回复'wifi'或者'上网'可以再次获取上网权限,回复'帮助'获取更多信息";
                    // $this->sendMsg ( $fromUsername, $toUsername, $contentStr );
                    // 	break;
                case 'unsubscribe':
                    //取消订阅
                    // 获得发送方名称
                    $user = $this->loadUserInfo($fromUsername);
                    // 获得发送方的id
                    $uid   = $user['id'];
                    $build = MemberModel::select();
                    $build->getConnection()->beginTransaction();
                    $status = \WifiAdmin\AuthListModel::whereUId($uid)->update(['uid' => $uid, 'is_delete' => 1]);
                    // 删除用户
                    $status1 = MemberModel::whereId($uid)->update(['id' => $uid, 'is_delete' => 1]);
                    if ($status && $status1) {
                        // 提交事务
                        $build->getConnection()->commit();
                    } else {
                        // 回滚事务
                        $build->getConnection()->rollBack();
                    }
                    break;
                case 'CLICK':
                    if (!empty ($three)) {
                        // 获得关键字
                        $key = trim($postObj->EventKey);
                        // 关键字不为空
                        if ($key == '上网' || $key == 'wifi') {
                            // 发送信息给用户
                            // 获取微信自动认证地址
                            $url = $this->getTokenUrl($shop, $fromUsername);
                            // 发送通过认证后的首条信息
                            $contentStr = '欢迎光临 ' . $shop['shop_name'] . '，上网请直接点击：<a target="_blank"  href="' . $url . '">我要上网</a>,回复"wifi"或者"上网"可以再次获取上网权限,回复"帮助"获取更多信息';
                            $this->sendMsg($fromUsername, $toUsername, $contentStr);
                        }
                    }
                    break;
                default:
                    echo $three;
                    break;
            }
        }
        // 获得发送过来得文本内容
        $keyword = trim($postObj->Content);
        if ($keyword) {
            // 关键字为wifi或上网
            if ($keyword == 'wifi' || $keyword == '上网') {
                // 当数据库中不存在该商家
                if (empty($shop)) {
                    $result_error = '商家已经取消微信关注上网功能';
                    $this->sendMsg($fromUsername, $toUsername, $result_error);
                } else {
                    //获得认证模式
                    $a = $this->getAuth($shop['auth_mode']);
                    if ($a == false) {
                        // 转发到第三方
                        $rs = $this->sendThreePlatform($shop, $postStr);
                        if (!empty ($rs)) {
                            echo $rs;
                        }
                        exit();
                    }
                }
                // 获取微信自动认证地址
                $url = $this->getTokenUrl($shop, $fromUsername);
                // 发送通过认证后的首条信息
                $contentStr = '欢迎光临 ' . $shop['shop_name'] . '，上网请直接点击：<a target="_blank"  href="' . $url . '">我要上网</a>,回复"wifi"或者"上网"可以再次获取上网权限,回复"帮助"获取更多信息';
                $this->sendMsg($fromUsername, $toUsername, $contentStr);
            } else if ($keyword == '帮助') {
                $contentStr = '回复"wifi"或者"上网"可以获取上网权限';
                $this->sendMsg($fromUsername, $toUsername, $contentStr);
            } else {
                // 转发到第三方
                $rs = $this->sendThreePlatform($shop, $postStr);
                if ($rs) {
                    // 输出第三方的内容
                    echo $rs;
                    exit();
                }
                echo '';
            }
        } else {
            // 通过第三方
            $rs = $this->sendThreePlatform($shop, $postStr);
            if ($rs) {
                echo $rs;
                exit ();
            }
            echo '';
        }
    }

    /**
     * 获得上网认证方式
     *
     * @param $authMode
     *
     * @return bool
     */
    private function getAuth($authMode)
    {
        // 获得当前商家上网的认证方式
        $tmp = explode('#', $authMode);
        foreach ($tmp as $v) {
            if ($v != '#' && $v != '') {
                $arr [] = $v;
            }
        }
        foreach ($arr as $v) {
            $temp = explode('=', $v);
            // 微信密码认证
            if (count($temp) > 1 && $temp [0] == '3') {
                return true;
                //微信关注认证
            } else if ($v == '4') {
                return true;
            }
        }

        return false;
    }

    /**
     * 第三方微信平台接入
     *
     * @param $shop    关注的商家
     * @param $postStr 发送的信息
     *
     * @return mixed|null|string
     */
    private function sendThreePlatform($shop, $postStr)
    {
        if ($shop['t_wx_token']) {
            $nonce     = mt_rand(1, 1000);
            $timestamp = time();
            $tmpArr    = [$shop['t_wx_token'], $timestamp, $nonce];
            sort($tmpArr, SORT_STRING);
            $tmpStr    = implode($tmpArr);
            // 路由服务地址
            $urls          = explode("?", $shop['t_wx_url']);
            $data          = 'timestamp=' . $timestamp . '&signature=' . sha1($tmpStr) . '&nonce=' . $nonce;
            if ($urls[1]) {
                $data = $data . '&' . $urls [1];
            }
            $remote_server = $urls [0] . '?' . $data;

            return $this->request_by_other($remote_server, $postStr);
        }

        return null;
    }

    /**
     * @param $url
     * @param $data
     *
     * @return mixed|string
     */
    public function requestByOther($url, $data)
    {
        $file_contents = '';
        // 判断函数file_get_contents是否已开通
        if (function_exists('file_get_contents')) {
            $opts    = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencodedrn' . 'Content-Length: ' . strlen($data) . "rn",
                    'content' => $data
                ]
            ];
            $context = stream_context_create($opts);
            $html    = null;
            for ($i = 0; $i < 5; $i++) {
                $file_contents = @file_get_contents($url, false, $context);
                //php.ini中，有这样两个选项:allow_url_fopen =on(表示可以通过url打开远程文件)，user_agent="PHP"（表示通过哪种脚本访问网络，默认前面有个 " ; " 去掉即可。）重启服务器。
                if ($file_contents) {
                    break;
                }
            }
        }
        if (!$file_contents) {
            $ch      = curl_init();
            $header  = 'Content-type: application/x-www-form-urlencodedrn' . 'Content-Length: ' . strlen($data) . "rn";
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //设置HTTP头
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $file_contents = curl_exec($ch);
            if (curl_errno($ch)) { //出错则显示错误信息
                $file_contents = curl_error($ch);
            }
            curl_close($ch);
        }

        return $file_contents;
    }

    /**
     * 发送信息
     *
     * @param $fromUsername
     * @param $toUsername
     * @param $contentStr
     */
    private function sendMsg($fromUsername, $toUsername, $contentStr)
    {
        $time      = time();
        $textTpl   = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Content><![CDATA[%s]]></Content>
						<FuncFlag>0</FuncFlag>
						</xml>";
        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, 'text', $contentStr);
        echo $resultStr;
        exit ();
    }

    /**
     * 检测签名
     *
     * @param $weiCode 已经加密的签名
     *
     * @return bool
     */
    private function checkSignature($weiCode)
    {
        $shop = $this->loadShopInfo($weiCode);
        if (!$shop) {
            return false;
        }
        $token = $shop['wx_token'];
        if (empty ($token)) { //没有设置token
            return false;
        }
        $signature = $this->request->getGet('signature') ? $this->request->getGet('signature') : '';
        $timestamp = $this->request->getGet('timestamp') ? $this->request->getGet('timestamp') : '';
        $nonce     = $this->request->getGet('nonce') ? $this->request->getGet('nonce') : '';
        $tmpArr    = [$token, $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取微信自动认证地址
     *
     * @param $shop
     * @param $openId
     *
     * @return string
     */
    private function getTokenUrl($shop, $openId)
    {
        $token = $this->checkOpenId($shop, $openId);
        $url      = "http://www.baidu.com/?token=" . $token; //
        return $url;
    }

    /**
     * 检测openID
     *
     * @param $shop   商户信息
     * @param $openId 公众平台账号
     *
     * @return string
     */
    private function checkOpenId($shop, $openId)
    {
        // 获得所有关注某商家公众平台的用户
        $info = MemberModel::select('*')->whereOpenId($openId)->get()->toArray();
        $info = $info ? $info[0] : [];
        // 不存在关注当前商家公众平台的用户
        if ($info || !$info['token']) {
            $addData['token']       = md5(uniqid());
            $addData['user']        = md5(uniqid());
            $addData['password']    = md5($openId);
            $addData['shop_id']     = $shop['id'];
            $addData['browser']     = 'weiXin';
            $addData['mode']        = 3;
            $addData['create_time'] = $addData['update_time'] = $addData['login_time'] = time();
            $addData['open_id']     = $openId;
            $addData['shop_wx_id']  = $shop['wx_id'];
            $userInfo               = $this->loadUserInfo($openId);
            $data['uid']            = $userInfo['id'];
            $data['create_date']    = time();
            $data['over_time']      = $this->getLimitTime($shop);
            $data['update_time']    = $data['login_time'] = $data['last_time'] = time();
            $data['shop_id']        = $shop['id'];
            $data['token']          = $addData['token'];
        } else {
            // 删除认证异常用户
            MemberModel::update(['is_delete' => 1])->whereToken($info[0]['token']);
            // 删除认证异常用户
            $addData['token']       = md5(uniqid());
            $addData['user']        = md5(uniqid());
            $addData['password']    = md5($openId);
            $addData['shop_id']     = $shop['id'];
            $addData['browser']     = 'weiXin';
            $addData['mode']        = 3;
            $addData['create_time'] = $addData['update_time'] = $addData['login_time'] = time();
            $addData['open_id']     = $openId;
            $addData['shop_wx_id']  = $shop['wx_id'];
            $userInfo               = $this->loadUserInfo($openId);
            $data['uid']            = $userInfo['id'];
            $data['create_time']    = time();
            $data['over_time']      = $this->getLimitTime($shop);
            $data['update_time']    = $data['login_time'] = $data['last_time'] = time();
            $data['shop_id']        = $shop['id'];
            $data['token']          = $addData['token'];
        }
        $build = MemberModel::select();
        $build->getConnection()->beginTransaction();
        $status  = MemberModel::insertGetId($addData);
        $status1 = \WifiAdmin\AuthListModel::insertGetId($data);
        if ($status && $status1) {
            // 提交事务
            $build->getConnection()->commit();

            return $addData['token'];
        } else {
            // 回滚事务
            $build->getConnection()->rollBack();
        }

        return '';
    }

    /**
     * 获得上网限制时间
     *
     * @param $shop 商家信息
     *
     * @return int|string
     */
    private function getLimitTime($shop)
    {
        $date = '';
        if ($shop['time_limit'] != '' && $shop['time_limit'] != '0') {
            $dt   = new YP_Date(time());
            $date = $dt->dateAdd($shop['time_limit'], 'n'); //默认7天试用期
        }

        return $date ? strtotime($date) : $date;
    }

    /**
     * 加载商家信息
     *
     * @param $weiCode
     *
     * @return mixed
     */
    private function loadShopInfo($weiCode)
    {
        $shop = \ShopModel::select('*')->whereWxId($weiCode)->get()->toArray();

        return $shop ? $shop[0] : [];
    }

    /**
     * 加载用户信息
     *
     * @param $openId
     *
     * @return mixed
     */
    private function loadUserInfo($openId)
    {
        $userInfo = MemberModel::select([
            'user',
            'mode',
            'shop_id',
            'route_id',
            'token',
            'phone',
            'qq',
            'mac',
            'login_time',
            'login_count',
            'login_ip',
            'browser',
            'online_time',
            'open_id',
            'shop_wx_id'
        ])->wherOopenId($openId)->get()->toArray();

        return $userInfo ? $userInfo[0] : [];
    }
}