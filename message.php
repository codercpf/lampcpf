<?php 
	include 'dbinfo/conn.inc.php';
	include 'dbinfo/ufun.inc.php';

	$openid = $_GET['openid'];
	$user = getUserInfo($openid);

	if(isset($_POST['dosubmit'])){
		//公众号向用户发送消息
		sendText($openid,$_POST['text']);
		// 用方法写入表，1是公众号回复客户的
		insertmessage($openid, $_POST['text'] , 1, "text");
	}


	

	$sqlUpdate = "update user set message='0' where openid= '{$openid}'";	
	//通过openid获取用的消息
	mysql_query($sqlUpdate);


	//查询所有这个用户和公众号对话的消息
	$sql = "select * from message where openid='{$openid}'";
	$result = mysql_query($sql);
	echo "<table border='1' width='600'>";
	while ($mess=mysql_fetch_assoc($result)) {
		echo "<tr>";
		if($mess['who']==0){
			echo '<td align="left"><img width="60" src="'.$user['headimgurl'].'">'.$user['nickname'].'<br/>'.$mess['mess'].'</td>';
		}else{
			echo '<td align="right">'.$mess['mess'].'：公众号</td>';
		}
		echo "</tr>";
	}
	echo "</table>";
 ?>
 <form action="message.php?openid=<?php echo $openid ?>" method="post">
 	<textarea name="text" cols="40" rows="6"></textarea>
 	<input type="submit" name="dosubmit" value="回复">
 </form>
 <br/>
 <a href="userinfo.php">返回用户列表</a>