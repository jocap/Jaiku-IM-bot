<?php
switch ($_REQUEST['step']) {
case 1:
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://gotralla.com/jaiku/index.php?from=mobile&message=".$_REQUEST['value0']);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 100);
$xml = curl_exec($ch);
if (curl_error($ch)) {
print "It's been an error while posting: ". curl_error($ch) ."\n<br/>";
}
curl_close($ch);
echo "Sent! <reset>";
break;
}
?>
