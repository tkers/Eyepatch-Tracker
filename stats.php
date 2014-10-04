<?php
header('Content-type: text/plain');

# Some configuration variables
$interval = 60*10; // Contact interval (in seconds)
$interval_idle = $interval + 60; //Timeout interval (in seconds)

require('config.php');
$link = mysql_connect($db['host'], $db['username'], $db['password']);
mysql_select_db($db['database']);

//$res = mysql_query('SELECT count(distinct ip) FROM peers WHERE lastcontact > '.(time()-$interval_idle));
$res = mysql_query('SELECT count(DISTINCT ip, port) FROM peers WHERE lastcontact > '.(time()-$interval_idle));
$peers = (int) mysql_result($res, 0, 0);

//$peers = rand(63,78);

$json = array('peers' => $peers);
echo json_encode($json);
?>