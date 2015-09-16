<?php 

	 loggerc("\r\n"."到达调用的文件\n");

	//openid
	//用户说的内容
	// 获取和你聊天的用户的基本信息，形成列表
	function getUserInfo($openid, $text)
    {

        loggerc("\r\n"."新文件\n");
        $access_token=get_token();

        loggerc("\r\n"."你的token：\n".$access_token);

        // 用http的get请求
        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";

        loggerc("\r\n"."获取用户信息的URL：\n".$url);
        
        // 请求获取用户信息的接口，返回这个openid对应的用于信息，json格式
        // $jsoninfo = httpRequest($url);//用作者自己写的额函数
        $jsoninfo = https_request($url);       

         loggerc("\r\n"."httpRequest处理后的用户信息：\n".$jsoninfo);

        // 将json装成php的数组，就可以使用数组操作用户信息
        $user = json_decode($jsoninfo,true);

        insertuser($user);
    }

        // 将用户的消息写入数据库
    function insertuser($user){
        include "conn.inc.php";
        $sql = "insert into user(openid,nickname,sex,city,province,headimgurl,utime) 
                values('{$user["openid"]}','{$user["nickname"]}','{$user["sex"]}','{$user["city"]}','{$user["province"]}','{$user["headimgurl"]}','".time()."')";
        mysql_query($sql);
    }



	// 通过https中的get或post
    function https_request($url, $data=null)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,false);
        
        if(!empty($data)){
            curl_setopt($curl, CURLOPT_POST,1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);

        $output = curl_exec($curl);
        return $output;
    }


	// 获取token
    function get_token()
    {
        $appid="wx320b480f654dfc25";
        $secret="5c5472e5778ef5d411e664d921ca6dd3";

        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";

        loggerc("\r\n得到token的URL：".$url);
        
        $json = https_request($url);
        $arr = json_decode($json, true);
        // print_r($arr);
        // echo "<br/><br/>";
        loggerc("\r\n得到的token：".$arr['access_token']);

        return $arr['access_token'];
    }

     //写日志，参数log_content传日志内容
    function loggerc($log_content)
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


 ?>