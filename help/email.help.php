<?php

namespace Yoqeen\Help;

/**
* 邮件发送类
* 支持发送纯文本邮件和HTML格式的邮件，可以多收件人，多抄送，多秘密抄送，带附件的邮件
* 需要的php扩展，sockets和Fileinfo。
* example
* $mail = new Email();
* $mail->setServer("XXXXX", "XXXXX@XXXXX", "XXXXX", "XXX"); 设置smtp服务器
* $mail->setFrom("XXXXX"); 设置发件人
* $mail->setReceiver("XXXXX"); 设置收件人，多个收件人，调用多次
* $mail->setCc("XXXX"); 设置抄送，多个抄送，调用多次
* $mail->setBcc("XXXXX"); 设置秘密抄送，多个秘密抄送，调用多次
* $mail->setMailInfo("test", "<b>test</b>"); 设置邮件主题、内容
* $mail->send(); 发送
*/

class Email
{
	/**
    * 邮件传输代理用户名
    */
    private $_userName;

    /**
    * 邮件传输代理密码
    */
    private $_password;

    /**
    * 邮件传输代理服务器地址
    */
    protected $_sendServer;

    /**
    * 邮件传输代理服务器端口
    */
    protected $_port=25;

    /**
    * 发件人
    */
    protected $_from;

    /**
    * 发件人名称
    */
    protected $_fromname;

    /**
    * string 收件人
    */
    protected $_to;

    /**
    * string 抄送
    */
    protected $_cc;

    /**
    * string 秘密抄送
    */
    protected $_bcc;

    /**
    * string 主题
    */
    protected $_subject;

    /**
    * 邮件正文
    */
    protected $_body;

    /**
    * 附件
    */
    protected $_attachment;

    /**
    * socket资源
    */
    protected $_socket;

    /**
    * 错误信息
    */
    protected $_errorMessage;

    /**
    * 设置邮件传输代理，如果是可以匿名发送有邮件的服务器，只需传递代理服务器地址就行
    * $server 代理服务器的ip或者域名
    * $username 认证账号
    * $password 认证密码
    * $port 代理服务器的端口，smtp默认465号端口
    */
    public function setServer($server, $username="", $password="", $port='25')
    {
        $this->_sendServer = $server;
        $this->_port = $port;
        if(!empty($username))
        {
            $this->_userName = base64_encode($username);
        }
        if(!empty($password))
        {
            $this->_password = base64_encode($password);
        }
        return true;
    }

    /**
    * 设置发件人
    * $from 发件人地址
    */
    public function setFrom($from)
    {
        $this->_from = $from;
        return true;
    }

    /**
    * 设置发件人
    * $from 发件人地址
    */
    public function setFromName($fromname)
    {
        $this->_fromname = $fromname;
        return true;
    }

    /**
    * 设置收件人，多个收件人，连续调用多次
    * $to 收件人地址
    */
    public function setReceiver($to)
    {
        if(isset($this->_to)) 
        {
            if(is_string($this->_to))
            {
                $this->_to = array($this->_to);
                $this->_to[] = $to;
                return true;
            }
            elseif(is_array($this->_to))
            {
                $this->_to[] = $to;
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            $this->_to = $to;
            return true;
        }
    }

    /**
    * 设置抄送，多个抄送，连续调用多次
    * $cc 抄送地址
    */
    public function setCc($cc)
    {
        if(isset($this->_cc))
        {
            if(is_string($this->_cc))
            {
                $this->_cc = array($this->_cc);
                $this->_cc[] = $cc;
                return true;
            }
            elseif(is_array($this->_cc))
            {
                $this->_cc[] = $cc;
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            $this->_cc = $cc;
            return true;
        }
    }

    /**
    * 设置秘密抄送，多个秘密抄送，连续调用多次
    * $bcc 秘密抄送地址
    */
    public function setBcc($bcc)
    {
        if(isset($this->_bcc))
        {
            if(is_string($this->_bcc))
            {
                $this->_bcc = array($this->_bcc);
                $this->_bcc[] = $bcc;
                return true;
            }
            elseif(is_array($this->_bcc))
            {
                $this->_bcc[] = $bcc;
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            $this->_bcc = $bcc;
            return true;
        }
    }

    /**
    * 设置邮件信息
    * $body 邮件主题
    * $subject 邮件主体内容，可以是纯文本，也可是是HTML文本
    * $attachment 附件，文件地址
    */
    public function setMailInfo($subject, $body, $attachment="")
    {
        $this->_subject = $subject;
        $this->_body = base64_encode($body);
        if(!empty($attachment))
        {
            $this->_attachment = $attachment;
        }
        return true;
    }

    /**
    * 发送邮件
    */
    public function send()
    {
        $command = $this->getCommand();
        $this->socket();

        foreach ($command as $value)
        {
            if($this->sendCommand($value[0], $value[1]))
            {
                continue;
            }
            else
            {
                return false;
            }
        }

        //其实这里也没必要关闭，smtp命令：QUIT发出之后，服务器就关闭了连接，本地的socket资源会自动释放
        $this->close(); 
    //    echo 'Mail OK!';
        return true;
    }

    /**
    * 返回错误信息
    */
    public function error()
    {
        if(!isset($this->_errorMessage))
        {
            $this->_errorMessage = "";
        }
        return $this->_errorMessage;
    }

    /**
    * 返回mail命令
    */
    protected function getCommand()
    {
        $command = array(
            array("HELO email\r\n", 250)
        );
        if(!empty($this->_userName))
        {
            $command[] = array("AUTH LOGIN\r\n", 334);
            $command[] = array($this->_userName . "\r\n", 334);
            $command[] = array($this->_password . "\r\n", 235);
        }
        $command[] = array("MAIL FROM:<" . $this->_from . ">\r\n", 250);

        $separator = "----=_Part_" . md5($this->_from . time()) . uniqid();
        //设置发件人
        $header = "FROM: ".$this->_fromname."<" . $this->_from . ">\r\n";

        //设置收件人
        if(is_array($this->_to))
        {
            $count = count($this->_to);
            for($i=0; $i<$count; $i++)
            {
                $command[] = array("RCPT TO: <" . $this->_to[$i] . ">\r\n", 250);
                if($i == 0)
                {
                    $header .= "TO: <" . $this->_to[$i] .">";
                }
                elseif($i + 1 == $count)
                {
                    $header .= ",<" . $this->_to[$i] .">\r\n";
                }
                else
                {
                    $header .= ",<" . $this->_to[$i] .">";
                }
            }
        }
        else
        {
            $command[] = array("RCPT TO: <" . $this->_to . ">\r\n", 250);
            $header .= "TO: <" . $this->_to . ">\r\n";
        }

        //设置抄送
        if(isset($this->_cc))
        {
            if(is_array($this->_cc))
            {
                $count = count($this->_cc);
                for($i=0; $i<$count; $i++)
                {
                    $command[] = array("RCPT TO: <" . $this->_cc[$i] . ">\r\n", 250);
                    if($i == 0)
                    {
                    	$header .= "CC: <" . $this->_cc[$i] .">";
                    }
                    elseif($i + 1 == $count)
                    {
                        $header .= ",<" . $this->_cc[$i] .">\r\n";
                    }
                    else
                    {
                        $header .= ",<" . $this->_cc[$i] .">";
                    }
                }
            }
            else
            {
                $command[] = array("RCPT TO: <" . $this->_cc . ">\r\n", 250);
                $header .= "CC: <" . $this->_cc . ">\r\n";
            }
        }

        //设置秘密抄送
        if(isset($this->_bcc))
        {
            if(is_array($this->_bcc))
            {
                $count = count($this->_bcc);
                for($i=0; $i<$count; $i++)
                {
                    $command[] = array("RCPT TO: <" . $this->_bcc[$i] . ">\r\n", 250);
                    if($i == 0)
                    {
                    	$header .= "BCC: <" . $this->_bcc[$i] .">";
                    }
                    elseif($i + 1 == $count)
                    {
                        $header .= ",<" . $this->_bcc[$i] .">\r\n";
                    }
                    else
                    {
                        $header .= ",<" . $this->_bcc[$i] .">";
                    }
                }
            }
            else
            {
                $command[] = array("RCPT TO: <" . $this->_bcc . ">\r\n", 250);
                $header .= "BCC: <" . $this->_bcc . ">\r\n";
            }
        }

        $header .= "Subject: " . $this->_subject ."\r\n";
        if(isset($this->_attachment))
        {
            //含有附件的邮件头需要声明成这个
            $header .= "Content-Type: multipart/mixed;\r\n";
        }
        elseif(false)
        {
            //邮件体含有图片资源的需要声明成这个
            $header .= "Content-Type: multipart/related;\r\n";
        }
        else
        {
            //html或者纯文本的邮件声明成这个
            $header .= "Content-Type: multipart/alternative;\r\n";
        }

        //邮件头分隔符
        $header .= "\t" . 'boundary="' . $separator . '"';
        $header .= "\r\nMIME-Version: 1.0\r\n";
        $header .= "\r\n--" . $separator . "\r\n";
        $header .= "Content-Type:text/html; charset=utf-8\r\n";
        $header .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $header .= $this->_body . "\r\n";
        $header .= "--" . $separator . "\r\n";

        //加入附件
        if(isset($this->_attachment))
        {
            $header .= "\r\n--" . $separator . "\r\n";
            $header .= "Content-Type: " . $this->getMIMEType() . '; name="' . basename($this->_attachment) . '"' . "\r\n";
            $header .= "Content-Transfer-Encoding: base64\r\n";
            $header .= 'Content-Disposition: attachment; filename="' . basename($this->_attachment) . '"' . "\r\n";
            $header .= "\r\n";
            $header .= $this->readFile();
            $header .= "\r\n--" . $separator . "\r\n";
        }

        $header .= "\r\n.\r\n";

        $command[] = array("DATA\r\n", 354);
        $command[] = array($header, 250);
        $command[] = array("QUIT\r\n", 221);

        return $command;
    }

    /**
    * 发送命令
    * $command 发送到服务器的smtp命令
    * $code 期望服务器返回的响应吗
    */
    protected function sendCommand($command, $code)
    {
    //    echo 'Send command:' . $command . ',expected code:' . $code . '<br />';
        //发送命令给服务器
        try{
            if(socket_write($this->_socket, $command, strlen($command)))
            {

                //当邮件内容分多次发送时，没有$code，服务器没有返回
                if(empty($code))
                {
                    return true;
                }

                //读取服务器返回
                $data = trim(socket_read($this->_socket, 1024));
    //            echo 'response:' . $data . '<br /><br />';

                if($data)
                {
                    $pattern = "/^".$code."/";
                    if(preg_match($pattern, $data))
                    {
                        return true;
                    }
                    else
                    {
                        $this->_errorMessage = "Error:" . $data . "|**| command:";
                        return false;
                    }
                }
                else
                {
                    $this->_errorMessage = "Error:" . socket_strerror(socket_last_error());
                    return false;
                }
            }
            else
            {
                $this->_errorMessage = "Error:" . socket_strerror(socket_last_error());
                return false;
            }
        }catch(Exception $e) {
            $this->_errorMessage = "Error:" . $e->getMessage();
        }
    }

    /**
    * 读取附件文件内容，返回base64编码后的文件内容
    */
    protected function readFile()
    {
        if(isset($this->_attachment) && file_exists($this->_attachment))
        {
            $file = file_get_contents($this->_attachment);
            return base64_encode($file);
        }
        else
        {
            return false;
        }
    }

    /**
    * 获取附件MIME类型
    */
    protected function getMIMEType()
    {
        if(isset($this->_attachment) && file_exists($this->_attachment))
        {
            $mime = mime_content_type($this->_attachment);
            if(! preg_match("/gif|jpg|png|jpeg/", $mime))
            {
                $mime = "application/octet-stream";
            }
            return $mime;
        }
        else
        {
            return false;
        }
    }

    /**
    * 建立到服务器的网络连接
    */
    private function socket()
    {
        if(!function_exists("socket_create"))
        {
            $this->_errorMessage = "Extension sockets must be enabled";
            return false;
        }
        //创建socket资源
        $this->_socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));

        if(!$this->_socket)
        {
            $this->_errorMessage = socket_strerror(socket_last_error());
            return false;
        }

        socket_set_block($this->_socket);//设置阻塞模式

        //连接服务器
        if(!socket_connect($this->_socket, $this->_sendServer, $this->_port))
        {
            $this->_errorMessage = socket_strerror(socket_last_error());
            return false;
        }
        socket_read($this->_socket, 1024);

        return true;
    }

    /**
    * 关闭socket
    */
    private function close()
    {
        if(isset($this->_socket) && is_object($this->_socket))
        {
            $this->_socket->close();
            return true;
        }
        $this->_errorMessage = "No resource can to be close";
        return false;
    }

    public function mail($email, $title, $content)
    {
        foreach(EMAIL_NOREPLY as $from)
        {
            if($this->sendMail($from, $email, $title, $content))
            {
                return true;
            }
        }
        return false;
    }

    public function sendMail($from, $email, $title, $content)
    {
        $this->setServer(EMAIL_SERVER, $from, EMAIL_NOREPLY_PASSWORD, EMAIL_PORT);
        $this->setFrom($from);
        $this->setFromName("说否");
        $this->setReceiver($email);
        $this->setMailInfo("$title", $content);
        return $this->send();
    }
}