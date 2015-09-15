<?php 
	$con = mysql_connect('localhost','root','root2015');
	if(!$con){
		echo "Error:".mysql_error($con);
	}else
	{
		echo "Connected successfully!<br/>";
	}

	$sql="create table user(
		openid char(100),
		nickname varchar(50),
		sex char(10),
		city char(50),
		province char(50),
		headimgurl varchar(200),
		utime int,
		primary key (openid)
		)default charset='utf8'";

	$sql02="create table message(
		openid char(100),
		mess varchar(255),
		who int default 0,
		utime int,
		mtype varchar(30)
		)default charset='utf8'";

	mysql_select_db('wx2',$con);
	if (mysql_query($sql02)) {
		echo "table created successfully!<br/>";
	}else{
		echo "Error:".mysql_error($con)."<br/>";
	}
 ?>