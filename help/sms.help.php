<?php

namespace Yoqeen\Help;

/**
 | 阿里短信类
 */
class Sms
{
    /**
     * API调用地址
     */
    protected $host = 'http://sms.market.alicloudapi.com';

    /**
     * API链接
     */
    protected $url;

    /**
     * API KEY
     */
    protected $apiKey;

    /**
     * API SECRET
     */
    protected $apiSecret;

    /**
     * APP CODE
     */
    protected $appCode = 'd599c6ffeb244954b56fa8119ac4698b';

    /**
     * 手机号，多个手机号是数组
     */
    protected $mobiles;

    /**
     * 信息模版变量
     * 数组类型
     */
    protected $paramString;

    /**
     * 信息模版CODE
     */
    protected $templateCode;

    /**
     * header
     */
    protected $headers = array();

    /**
     * 验证码标签
     */
    protected $seKey = 'mobileCode';

    /**
     * 有效期时间，单位s
     */
    protected $expire = 900;

    /**
     * signName
     */
    protected $signName = '说否';

    /**
     * 验证码
     */
    protected $code;

    /**
     * 等待时间
     */
    protected $waittime = 60;

    function __construct()
    {
    }

    /**
     * 初始化
     */
    protected function init()
    {
        $this->setHeader();
        // $this->code();
        // $this->paramString();
        $this->buildParams();
    }

    /**
     * 发送信息
     * @return [type] [description]
     */
    public function send()
    {
        // $this->mobiles = $mobiles;
        $this->init();
        return request($this->url, '', $this->headers);
    }

    /**
     * 发送验证码
     * @return [type] [description]
     */
    public function sendCode($mobiles)
    {
        $this ->setTemplateCode('SMS_58315084');
        $this ->setMobiles($mobiles);
        $this ->code();
        $this ->setParamString(['n'=>(string)$this->getCode()]);
        return json_decode($this->send());
    }

    /**
     * 发送悄悄话通知
     */
    public function sendTalkMessage($mobiles, $to, $code)
    {
        $this ->setTemplateCode('SMS_67176573');
        $this ->setMobiles($mobiles);
        $this ->setParamString(['touser'=>(string)$to, 'code'=>(string)$code]);
        return json_decode($this ->send());
    }

    /**
     * 设置头部信息
     */
    protected function setHeader()
    {
        array_push($this->headers, "Authorization:APPCODE " . $this->appCode);
    }

    /**
     * 拼接参数
     * @return [type] [description]
     */
    protected function buildParams()
    {
        $mobiles = is_array($this->mobiles) ? implode(',', $this->mobiles) : $this->mobiles;
        $params = urlencode(json_encode($this->paramString));
        $querys = "ParamString={$params}&RecNum={$mobiles}&SignName=".urlencode($this->signName)."&TemplateCode={$this->templateCode}";
        // die($querys);
        $this->url = $this->host.'/singleSendSms?'.$querys;
        // die($this->url);
    }

    /**
     * 生成验证码
     */
    public function code()
    {
        $this->code = mt_rand(123457,987653);
        $_SESSION['common'][$this->seKey]['code'] = md5($this->mobiles.$this->code); // 把校验码保存到session
        $_SESSION['common'][$this->seKey]['time'] = time();  // 验证码创建时间
        $_SESSION['common'][$this->seKey]['waittime'] = $this->waittime;
        setcookie('_mwtm', '1', time()+$this->waittime, '/');
    }

    /**
     * 验证验证码
     */
    public function check($mobile='',$code='')
    {
        // dd(md5($mobile.$code));
        if(!$mobile)
        {
            return false;
        }
        if(!$code || empty($_SESSION['common'][$this->seKey]))
        {
            return false;
        }
        if(time() - $_SESSION['common'][$this->seKey]['time'] > $this->expire)
        {
            unset($_SESSION[$this->seKey]);
            return false;
        }
        if(md5($mobile.$code) == $_SESSION['common'][$this->seKey]['code'])
        {
            return true;        
        }
        return false;   
    }

    public function __call($method, $args)
    {
        if(substr($method, 0, 3) == 'get')
        {
            $field = lcfirst(substr($method, 3));
            return $this->$field;
        }
        if(substr($method, 0, 3) == 'set')
        {
            $field = lcfirst(substr($method, 3));
            return $this->$field = $args[0];
        }
    }
}