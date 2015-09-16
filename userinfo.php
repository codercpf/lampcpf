<?php
	date_default_timezone_set('PRC');

	header("Content-Type:text/html;charset=utf-8");
	
	include 'dbinfo/conn.inc.php';
	$sql="select * from user order by utime desc";
	$result=mysql_query($sql);
	echo "<h1>用户会话列表</h1>";
	echo '<table border="1" width="80%">';

	while ($row=mysql_fetch_array($result)) {
		// 如果没有查看的记录，就显示绿色
		if($row['message']==0){
			$bg="";
		}else{
			$bg="green";
		}

		echo '<tr bgcolor="'.$bg.'">';
		echo '<td><img src="'.$row['headimgurl'].'" width="60" ></td>';
		echo '<td>'.$row['nickname'].'</td>';
		echo '<td>'.$row['province']." - ".$row['city'].'</td>';
		echo '<td>'.date("Y-m-d H:i:s", $row['utime']).'</td>';
		echo '<td><a href="message.php?openid='.$row['openid'].'">查看</a></td>';
		echo '</tr>';
	}

	echo '</table>';
 ?>