<meta charset="utf-8">
<?php 
	//用户分组
	include "dbinfo/ufun.inc.php";


	$access_token = get_token();
	$url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token={$access_token}";
	$output = https_request($url); 	//返回json格式
/*
	echo "<pre>";
	var_dump($output);
	echo "</pre>";
*/	//将返回来的Json转成数组操作

	$jsonTogroups = json_decode($output, true);
/*
	echo "<pre>";
	print_r($jsonTogroups);
	echo "</pre>";
*/	
	// 遍历数组形成分组列表
	echo '<ul>';
	foreach ($jsonTogroups['groups'] as $g) {
		echo '<li>'.$g['name'].'('.$g['count'].')&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="mgroup.php?name='.$g['name'].'&id='.$g['id'].'">修改</a></li>';
	}
	echo '</ul>';

	echo '<br><a href="create.php">创建分组</a>';
?>