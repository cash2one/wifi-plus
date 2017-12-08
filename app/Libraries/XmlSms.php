<?php
/**
 * User: yongli
 * Date: 17/12/7
 * Time: 10:50
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
require_once APP_PATH . 'ThirdParty/NuSoap/nusoap.php';

/**
 * Class XmlSms
 * 短信平台接口代码
 */
class XmlSms
{
    /**
     * 网关地址
     *
     * @var
     */
    public $url;

    /**
     * 帐号
     *
     * @var
     */
    public $user;

    /**
     * 密码
     *
     * @var unknown_type
     */
    public $password;

    /**
     * webservice客户端
     *
     * @var nusoap_client
     */
    public $soap;
    
    /**
     * 默认命名空间
     *
     * @var string
     */
    public $namespace = 'http://tempuri.org/';

    /**
     * 往外发送的内容的编码,默认为 GBK
     */
    public $outgoingEncoding = "GBK";

    /**
     * 往内发送的内容的编码,默认为 GBK
     */
    public $incomingEncoding = '';
    
    /**
     * XmlSms constructor.
     *
     * @param      $server
     * @param      $u
     * @param      $p
     * @param bool $proxyHost
     * @param bool $proxyPort
     * @param bool $proxyUserName
     * @param bool $proxyPassword
     * @param int  $timeout
     * @param int  $response_timeout
     */
    public function __construct(
        $server,
        $u,
        $p,
        $proxyHost = false,
        $proxyPort = false,
        $proxyUserName = false,
        $proxyPassword = false,
        $timeout = 2,
        $response_timeout = 30
    ) {
        $this->url      = $server;
        $this->user     = $u;
        $this->password = $p;
        /**
         * 初始化 webservice 客户端
         */
        $this->soap = new nusoap_client($server, true);
        // 默认往外设置编码为utf-8；
        $this->soap->soap_defencoding = 'GBK';
        $this->soap->decode_utf8      = false;

    }

    /**
     * 设置发送内容的字符编码
     *
     * @param string $outgoingEncoding 发送内容字符集编码
     */
    public function setOutgoingEncoding($outgoingEncoding)
    {
        $this->outgoingEncoding       = $outgoingEncoding;
        $this->soap->soap_defencoding = $this->outgoingEncoding;

    }

    /**
     * 设置账号
     *
     * @param $u
     */
    public function setUser($u)
    {
        $this->user = $u;
    }

    /**
     * 设置密码
     *
     * @param $p
     */
    public function setPwd($p)
    {
        $this->password = $p;
    }

    /**
     * 设置接收内容 的字符编码
     *
     * @param string $incomingEncoding 接收内容字符集编码
     */
    public function setIncomingEncoding($incomingEncoding)
    {
        $this->incomingEncoding   = $incomingEncoding;
        $this->soap->xml_encoding = $this->incomingEncoding;
    }

    /**
     * @param $ns
     */
    public function setNameSpace($ns)
    {
        $this->namespace = $ns;
    }

    public function test()
    {
        dump($this->soap->call('HelloWorld', []));
    }

    /**
     * 获取短信帐号余额
     */
    public function GetSmsAccount()
    {
        $params = ['key' => $this->user, 'pwd' => $this->password];
        $result = $this->soap->call("GetSmsAccount", $params);

        return $result['GetSmsAccountResult'];
    }

    /**
     * 获取短信单价
     */
    public function GetSmsPrice()
    {
        $params = ['key' => $this->user, 'pwd' => $this->password];
        $result = $this->soap->call("GetSmsPrice", $params);

        return $result['GetSmsPriceResult'];
    }

    /**
     * 发送短信
     *
     * @param $phones
     * @param $msg
     *
     * @return mixed
     */
    public function SendSms($phones, $msg)
    {
        $params = ['key' => $this->user, 'pwd' => $this->password, 'phone' => $phones, 'info' => $msg];
        $result = $this->soap->call("SmsSendMany", $params);

        return $result['SmsSendManyResult'];
    }
}