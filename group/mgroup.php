<?php 
	header("Content-Type:text/html;charset=utf-8");

	// 这个文件修改组名
	include 'dbinfo/ufun.inc.php';
	if (isset($_POST['dosubmit'])) {
		//access_token
		$access_token=get_token();

		//修改url
		$url="https://api.weixin.qq.com/cgi-bin/groups/update?access_token={$access_token}";

		//post传过去 组id和组名
		$jsonstr =  '{"group":{"id":'.$_POST['id'].',"name":"'.$_POST['name'].'"}}';

		//CURL请求
		$output = https_request($url,$jsonstr);
		
		// var_dump($result);
		//创建成功转到组列表
		header("Location:group.php");
	}

?>

<br>
<form action="mgroup.php" method="post">
	<div>
		<input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
		<label for="name">分组名称：</label>
		<input type="text" name="name" id="name" value="<?php echo $_GET['name'] ?>" tabindex="1" />
	</div>

	<div>
		<input type="submit" name="dosubmit" value="修改组名" />
	</div>
</form>