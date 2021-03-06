<?php

// require 'fci.php';


date_default_timezone_set('PRC');

/**
  * wechat php test
  */

//define your token
define("TOKEN", "jiekou");

// 通过Wechat创建一个对象，才能使用这个类中的成员
$wechatObj = new wechatTest();
if (!isset($_GET['echostr']))
{
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}


//在这个类中可以接收用户消息，可以相应用户的所有消息，token验证
class wechatTest
{
    // 处理token
    public function valid()
    {
        //通过get获取签名
        $signature = $_GET["signature"];
        // 时间戳
        $timestamp = $_GET["timestamp"];
        // 随机数
        $nonce = $_GET["nonce"];
        // token                
        $token = TOKEN;


        // 将接到的三个参数结合toaken，做成数组，
        $tmpArr = array($token, $timestamp, $nonce);
        // 对这个数组进行字典排序
        sort($tmpArr, SORT_STRING);
        // 排序后合成字符串
        $tmpStr = implode( $tmpArr );
        // 对合成的字符串进行加密
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    
    //专门用来相应用户信息——————写程序，写日志
    function responseMsg()
    {
        // $postStr=$GLOBALS['HTTP_RAW_POST_DATA'];     //接收的XML
        // file_put_contents("test.xml","abc",FILE_APPEND);

        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];  
        if (!empty($postStr)) { 

            $result = "";               //返回的消息XML

            //接收到的消息写入日志        
            $this->logger("R \r\n".$postStr);

            $postObj = simplexml_load_string($postStr,'SimpleXMLElement',LIBXML_NOCDATA);
            
            $r_Type = trim($postObj->MsgType);
            switch ($r_Type) {
                case 'text':        //接收文本消息
                    $result = $this->receiveText($postObj);                 
                    break;
                case 'image':
                    
                    break;
                case 'location':
                    
                    break;
                case 'voice':       //接收语音消息
                    $result = $this->receiveVoice($postObj);
                    break;
                case 'link':
                    
                    break;
                case 'video':
                    
                    break;
                default:
                    $result="unknown msg type:".$r_Type;
                    break;
            }

            // 回复给用户之前写入日志
            $this->logger("T \r\n".$result);

            // 输出消息给微信
            echo $result;
        }else{
            echo "";
            exit;
        }       
    }


    // 用curl请求天气接口
    function httpRequest($url)
    {
        $this->logger("\r\n传递的url:".$url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1); //是否抓取跳转后的页面,新加的
        $output = curl_exec($ch);

        $this->logger("\r\n得到的output01:".$output);

        curl_close($ch);
        if ($output === FALSE){
            return "cURL Error: ". curl_error($ch);
        }
        return $output;
        // print_r($output);
    }
    function getcity($carr)
    {
        include 'dbinfo/conn.inc.php';
        mysql_query("set names utf8");

        // $this->logger("\r\n接收的参数： ".$carr);
        $sql = "select cityCode from weather where cityName='$carr'";
        
        // echo $sql."<br/>";
        // $this->logger("\r\n查询的函数：".$sql);

        //执行SQL语句
        $result = mysql_query($sql);        
        while ($row = mysql_fetch_assoc($result)) {
            $arr = $row['cityCode'];
        }       

         // echo $arr;
        // $this->logger("\r\n调用的结果：".$arr);
        return $arr;
    }    
    // 根据citycode获取天气接口的数据
    function getWeatherInfo($cityCode)
    {
        //获取实时天气
        $url = "http://www.weather.com.cn/data/sk/".$cityCode.".html";
        $this->logger("\r\n天气预报地址： ".$url);

        //通过curl获取citycode对应的天气的JSON数据
        $output = $this->httpRequest($url);

        $this->logger("\r\n得到的output02:".$output);

        // 使用json_decode将返回来的json格式抓为PHP的数组
        $weather = json_decode($output, true);  
        $this->logger("\r\n得到的数组： ".count($weather));

        $info = $weather['weatherinfo'];

        $this->logger("\r\n天气预报内容： ".count($info));

        //形成图文数组
        $weatherArray = array();
        $weatherArray[] = array("Title"=>$info['city']."天气预报", "Description"=>"", "PicUrl"=>"", "Url" =>"");
        if ((int)$cityCode < 101340000){
            $result = "实况 温度：".$info['temp']."℃ 湿度：".$info['SD']." 风速：".$info['WD'].$info['WSE']."级";
            $weatherArray[] = array("Title"=>str_replace("%", "﹪", $result), "Description"=>"", "PicUrl"=>"", "Url" =>"");
        }
        return $weatherArray;
    }


    function receiveVoice($object)
    {
        if (isset($object->Recognition) && !empty($object->Recognition))
        {
            $text = $object->Recognition;
            $text = rtrim($text,'！');
            // $this->logger("\r\n接收的语音： ".$text);
            $cityCode = $this->getcity($text);
            $this->logger("\r\n城市代码： ".$cityCode);
            if (empty($cityCode)) {
                $content="没有找到你说的：(".$text.")中的天气消息";
                $result = $this->transmitText($object, $content);
                return $result;
            }

/*
            //如果citycodes是一个数组，循环调用函数获取多个天气的图文
            if(is_array($cityCode)){
                $content = array();
                foreach ($cityCode as $code) {
                    $content=array_merge($content,$this->getWeatherInfo($code))
                }
            }
*/
            $content = $this->getWeatherInfo($cityCode);
            $result = $this->transmitNews($object,$content);

            
        }else{
            // $this->logger("\r\n".$object->MediaId);
            $content = array("MediaId" => $object->MediaId);
            $result = $this->transmitVoice($object, $content);
        }
        return $result;
    }


    private function receiveEvent($object)
    {
        //临时定义一个变量，不同时间发生时，反馈给用户不同内容
        $content = "";

        //通过用户发送来的不同事件做处理
        switch ($object->Event) {   
            case 'subscribe':
                $content ="欢迎关注常鹏飞的测试账号！";
                break;
            
            default:
                # code...
                break;
        }

    }


    // 接受文本消息的函数
    function receiveText($object)
    {   
        //接收用户的发送内容，放到keyword这个变量中
        $keyword = trim($object->Content);
        if(strstr($keyword,"文本"))       //查询文本两个字自不在关键字里面
        {
            $content = "这是一个文本消息";          
            // $result = $this->transmitText($object,$content);

        }elseif (strstr($keyword,"单图文")) {
            $content=array();
            $content[]=array("Title"=>"这是一个标题","Description"=>"00","PicUrl"=>"http://139.129.128.130/a/image/1.jpg","Url"=>"http://baike.baidu.com/view/13123608.htm");

            $this->logger("\r\n content的值：".var_dump($content));
            // $result = $this->transmitNews($object,$content);

        }elseif (strstr($keyword,"图文") || strstr($keyword,"多图文")) {
            $content=array();
            //几个图文消息就几个$content[];
            $content[]=array("Title"=>"这是一个标题1","Description"=>"11","PicUrl"=>"http://139.129.128.130/a/image/1.jpg","Url"=>"http://baike.baidu.com/view/13123608.htm");
            $content[]=array("Title"=>"这是一个标题2","Description"=>"22","PicUrl"=>"http://139.129.128.130/a/image/2.jpg","Url"=>"http://baike.baidu.com/view/13123608.htm");
            $content[]=array("Title"=>"这是一个标题3","Description"=>"33","PicUrl"=>"http://139.129.128.130/a/image/2.jpg","Url"=>"http://baike.baidu.com/view/13123608.htm");
            $content[]=array("Title"=>"这是一个标题4","Description"=>"44","PicUrl"=>"http://139.129.128.130/a/image/2.jpg","Url"=>"http://baike.baidu.com/view/13123608.htm");
            $content[]=array("Title"=>"这是一个标题5","Description"=>"55","PicUrl"=>"http://139.129.128.130/a/image/2.jpg","Url"=>"http://baike.baidu.com/view/13123608.htm");
            
            //$this->logger("\r\n".var_dump($content));
            // $result = $this->transmitNews($object,$content);

        }elseif (strstr($keyword,"音乐")) {

            $content   = array();
            $content[] = array("Title" =>"小苹果","Description"=>"很好听","MusicUrl"=>"http://139.129.128.130/a/music/1.mp3","HQMusicUrl"=>"http://139.129.128.130/a/music/1.mp3" );
            $this->logger("\r\n我是音乐：".$content);              
           
        }else{
            $content="常鹏飞 ".date("Y-m-d H:i:s")." 技术支持";
            // $result = $this->transmitText($object,$content);
        }


        //判断单图文和多图文
        if(is_array($content)){
            if (isset($content[0]['PicUrl'])){
                $result = $this->transmitNews($object,$content);                            
            }elseif (isset($content[0]['MusicUrl'])){
                $result = $this->transmitMusic($object,$content);   
            }            
        }else{
            //$keyword: 你输入的文本
            //$object->FromUserName 获取用户的openId，

            // 调用一个方法，将openId和你输入的文本，使用这个函数处理 
                $this->logger("\r\n"."include之前");
            include 'dbinfo/ufun.inc.php';            
                $this->logger("\r\n"."开始调用");
            $user=getUserInfo($object->FromUserName);

            //用户一会话就讲用户信息放入表user;
            insertuser($user);
            //用户一会话就讲用户会话放入表message;
            insertmessage($object->FromUserName,$keyword,0,'text');
                $this->logger("\r\n"."结束调用");


            //用户一会话就讲用户信息放入user表
            // $this->insertuser($user);
            //用户一会话就讲用户会话放入message
            // $this->insertmessage($openid,$keyword,0,"text");


            // 给你回复的内容
            $result = $this->transmitText($object,$content);
        }
      
        return $result;
        $this->logger("\r\n"."函数返回的值：\n".$result);
    } 


    //回复音乐消息
    function transmitMusic($object,$musicArr)
    {
        $this->logger("\r\n"."我在前面aaa"); 
        $item_str="";
        $itemTpl="
            <Music>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <MusicUrl><![CDATA[%s]]></MusicUrl>
            <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
            </Music>
        ";

        $this->logger("\r\n"."我在前面");       
        foreach ($musicArr as $item) 
        {
            $item_str.=sprintf($itemTpl,$item['Title'],$item['Description'],$item['MusicUrl'],$item['HQMusicUrl']);
        }
        $this->logger("\r\n"."我在后面");   
        $musicTpl="
            <xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[music]]></MsgType>
                $item_str
            </xml>";

        return sprintf($musicTpl, $object->FromUserName,$object->ToUserName,time());

/*
        return sprintf($musicTpl, $object->FromUserName,$object->ToUserName,time(),
            $musicArr['Title'],$musicArr['Description'],
            $musicArr['MusicUrl'],$musicArr['HQMusicUrl']);
*/
    }




    //回复单图文的方法
    function transmitNews($object,$newArray)
    {
        if(!is_array($newArray)){
            return '';
        }
        $itemTpl="
            <item>
            <Title><![CDATA[%s]]></Title> 
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
            </item>
        ";
        $item_str="";
        foreach ($newArray as $item) {
            $item_str.=sprintf($itemTpl,$item['Title'],$item['Description'],$item['PicUrl'],$item['Url']);
        }

        $xmlTpl="
            <xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <ArticleCount>%s</ArticleCount>
            <Articles>
                $item_str
            </Articles>
            </xml>      
        ";
        return sprintf($xmlTpl, $object->FromUserName,$object->ToUserName,time(),count($newArray));

    }



    // 向用户回复文件的方法
    function transmitText($object,$content)
    {
        $xmlTpl="
            <xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>
        ";

        //$this->logger("\r\n"."你好你好！！！");

        return sprintf($xmlTpl,$object->FromUserName,$object->ToUserName,time(),$content);
    }






    //写日志，参数log_content传日志内容
    private function logger($log_content)
    {
        // 日志大小10000kb
        $max_size=10000;
        $log_filename="log.xml";
        if (file_exists($log_filename) && abs(filesize($log_filename))>$max_size)
        {
            unlink($log_filename);
        }

        //写如日志
        file_put_contents($log_filename, date("H:i:s")." ".$log_content."\r\n",FILE_APPEND);
    }
}

?>