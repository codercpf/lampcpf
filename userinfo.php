<?php
	header("Content-Type:text/html;charset=utf-8");
	
	include 'dbinfo/conn.inc.php';
	$sql="select * from user order by utime desc";
	$result=mysql_query($sql);
	echo "<h1>用户会话列表</h1>";
	echo '<table border="1" width="80%">';

	while ($row=mysql_fetch_array($result)) {
		echo '<tr>';
		echo '<td><img src="'.$row['headimgurl'].'" width="60" ></td>';
		echo '<td>'.$row['nickname'].'</td>';
		echo '<td>'.$row['province']." - ".$row['city'].'</td>';
		echo '<td>'.date("Y-m-d H:i:s", $row['utime']).'</td>';
		echo '<td><a href="">查看</a></td>';
		echo '</tr>';
	}

	echo '</table>';
 ?>