<?php 
	header("Content-Type:text/html;charset=utf-8");

	// 移动和用户分组
	include 'dbinfo/ufun.inc.php';

	$access_token=get_token();

	if (isset($_POST['dosubmit'])) {
		$url="https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token={$access_token}";
		//参数post  json
		$jsonStr = '{"openid":"'.$_POST['openid'].'","to_groupid":'.$_POST['gid'].'}';

		$result = https_request($url,$jsonStr);

		echo "<pre>";
		var_dump($result);
		echo "</pre>";
	}

?>

<br>
<form action="togroup.php" method="post">
	<input type="hidden" name="openid" value="<?php echo $_GET['openid']; ?>">
	移动到：
	<select name="gid">		
		<?php
			$url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token={$access_token}";
			$output = https_request($url); 	//返回json格式
			$jsonTogroups = json_decode($output, true);
			// 遍历数组形成分组列表				
			foreach ($jsonTogroups['groups'] as $g)
			{
				echo '<option value="'.$g['id'].'">'.$g['name'].'</option>';
			}	
		?>		
	</select>

	<div>
		<input type="submit" name="dosubmit" value="移动" />
	</div>
</form>