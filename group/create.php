<?php 
	header("Content-Type:text/html;charset=utf-8");

	// 这个文件创建分组
	include 'dbinfo/ufun.inc.php';
	if (isset($_POST['dosubmit'])) {
		//url上用的accesstoken		
		$access_token=get_token();
		$url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token={$access_token}";
		
		// post方式将组名传过去
		$jsonstr = '{"group":{"name":"'.$_POST['name'].'"}}';
		//请求这个借口，返回id和祖名的json格式
		$result = https_request($url,$jsonstr);

		// var_dump($result);
		//创建成功转到组列表
		header("Location:group.php");


	}

?>

<br>
<form action="create.php" method="post">
	<div>
		<label for="name">分组名称：</label>
		<input type="text" name="name" id="name" value="" tabindex="1" />
	</div>

	<div>
		<input type="submit" name="dosubmit" value="添加分组" />
	</div>
</form>