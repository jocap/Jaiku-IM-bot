<?php

include("config.php");

// If receiving SMS
if(isset($_GET['from'])){
	$key = $ekey;
	
	$from = $_GET['from'];
	$time = $_GET['time'];
	$message = $_GET['message'];
	$hmac = $_GET['hmac'];
	
	$hmac_message = $from . $time . $message;
	$hmac_local = base64_encode(hash_hmac('sha1', $hmac_message, $key,true));
	
	if($hmac == $hmac_local){
		$sql = "SELECT * FROM users WHERE number=$from";
		$result = mysql_query($sql) or die("SQL: $sql <br />".mysql_error());
		while ($r = mysql_fetch_array($result, MYSQL_BOTH)) {
			$user = array(
			$r['number'],
			$r['user'],
			$r['key']
			);
		}
	
		require_once("class.jaiku.php");
		$j = new Jaiku($user[1],$user[2]);
		$j->UpdatePresence($message);
	}
}

if(isset($_POST['submit'])){
	$_POST = db_escape($_POST);
	$user = $_POST['username'];
	$key = $_POST['key'];
	$number = $_POST['number'];
	
	// In this example, we've got 1 table inside our database; `users`, containing 3 fields; `number`, `user` and `key`.

	$sql = "SELECT COUNT(*) FROM $mysql_database.users WHERE number=$number";
	$result = mysql_query($sql);
	if (mysql_result($result, 0) > 0) {
		?>
		<script type="text/javascript">
		alert("You've already registered this phone number with the Jaiku SMS Service!");
		</script>
		<?php
		$already = true;
	}
	
	if($already!=true){
		$sql2 = "INSERT INTO  `$mysql_database`.`users` (
		`number` ,
		`user` ,
		`key`
		)
		VALUES (
		'$number',  '$user',  '$key'
		);
		";
		$result2 = mysql_query($sql2) or die("SQL: $sql2 <br />".mysql_error());
	}
	
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
	<title>Jaiku SMS Service</title>
	<link type="text/css" rel="stylesheet" media="all" href="style.css" />
	<meta http-equiv="Content-Language" content="sv" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style type="text/css">
	body {
		font-family: Helvetica, Arial, sans-serif;
		font-size: 13px;
		width: 225px;
		margin: 0 auto;
	}
	h1.title {
		font-size: 26px;
	}
	form span {
		text-transform: uppercase;
		font-size: 11px;
	}
	</style>
</head>
<body>
	<h1 class="title">Jaiku SMS Service</h1>
	<div class="content">
		<form class="form" method="post" action="index.php">
			<span>Mobile number:</span><br />
			<input type="text" id="number" name="number" value="" /><br />
			<span>Jaiku Username:</span><br />
			<input type="text" id="username" name="username" value="" /><br />
			<span>Jaiku API-key:</span><br />
			<input type="text" id="key" name="key" value="" /> (<a href="http://api.jaiku.com/key" title="Log in first">here!</a>)<br />
			<input type="submit" id="submit" name="submit" value="Send" />
		</form>
	</div>
</body>
</html>