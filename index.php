<?php

/*
 *  eyepat.ch tracker
 *  Tijn Kersjes
 *  15-3-2011
 */

$validrequest = false;
$requesttype = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : '';

if($requesttype == '/scrape'){ 
	if(isset($_GET['info_hash'])){
		$validrequest = true; // Scrape
	}
}
elseif($requesttype == '/announce' || $requesttype == ''){
	if(isset($_GET['info_hash']) && isset($_GET['peer_id']) && isset($_GET['port']) && isset($_GET['left'])){
		$validrequest = true; // Announce
	}
}

# Invalid request - end program
if(!$validrequest){
	header('Content-type: text/html');
	//header('HTTP/1.0 400 Bad Request');
	//header('Location: http://eyepat.ch/');
	include('logo.php');
	exit;
}

# Valid request - process data
header('Content-type: text/plain');
ignore_user_abort(true);

# Some configuration variables
$interval = 60*10; // Contact interval (in seconds)
$interval_idle = $interval + 60; //Timeout interval (in seconds)
$max_peers = 50; // Maximum number of peers to send

# Remove magic quotes
if(get_magic_quotes_gpc()){
	$_GET = array_map('stripslashes', $_GET);
}

# Some functions
require('becoder.php');
/*
function bencode($data){
	if(is_string($data)){
		$len = (string) strlen($data);
		$cdata = $len.':'.$data;
		return $cdata;
	}
	else if(is_int($data)){
		$cdata = 'i'.$data.'e';
		return $cdata;
	}
}

function bencodeList($array, $noencode = false){
	$str = 'l';
	sort($array);
	foreach($array AS $v){
		if($noencode){
			$str .= $v;
		}
		else{
			$str .= bencode($v);
		}
	}
	$str .= 'e';
	return $str;
}

function bencodeDict($array){
	$str = 'd';
	ksort($array);
	foreach($array AS $k => $v){
		$str .= bencode($k).bencode($v);
	}
	$str .= 'e';
	return $str;
}
*/

function dbEsc($value){
	return mysql_escape_string($value);
}

function scriptError($error_level, $error_message, $error_file, $error_line, $error_context){
	//trackerFail('ERROR at line '.$error_line.':'.$error_message);
	trackerFail('Tracker error, I don\'t blame you.');
	exit;
}

function trackerFail($msg){
	echo (new bencodeDict(array('failure reason' => $msg)));
	exit;
}

set_error_handler('scriptError');

require('config.php');
$link = mysql_connect($db['host'], $db['username'], $db['password']);
mysql_select_db($db['database']);

# Scrape data
$infohash = bin2hex($_GET['info_hash']);
$peer_ip = $_SERVER['REMOTE_ADDR'];

$res = mysql_query('SELECT seeder FROM peers WHERE infohash = "'.dbEsc($infohash).'" AND ip != "'.dbEsc($peer_ip).'" AND lastcontact > '.(time()-$interval_idle));

$scrape_peers = mysql_num_rows($res);
$scrape_leechers = $scrape_peers;
$scrape_seeders = 0;

while($row = mysql_fetch_row($res)){
	if($row[0] == 1){
		$scrape_seeders++;
		$scrape_leechers--;
	}
}

# Scrape request
if($requesttype == '/scrape'){
	$scrape_data = new bencodeDict(array('complete' => $scrape_seeders, 'incomplete' => $scrape_leechers));
	//$response = 'd'.bencode('files').'d'.bencode($_GET['info_hash']).bencodeDict($scrape).'e'.'e';
	$files_data = new bencodeDict(array($_GET['info_hash'] => $scrape_data));
	$response = new bencodeDict(array('files' => $files_data));
	echo $response;
	
	//mail('tijn@coffeecoders.nl', 'eyepat.ch tracker report', $peer_ip.' scraped for '.$infohash, 'From: eyepat.ch tracker <noreply@eyepat.ch');
	
	exit;
}

# Announce request
$peer_id = $_GET['peer_id'];
$peer_port = $_GET['port'];
$event = (isset($_GET['event'])) ? $_GET['event'] : '';
$isseeder = ($_GET['left'] == 0) ? 1 : 0;

# Remove stopped peer
if($event == 'stopped'){
	mysql_query('DELETE FROM peers WHERE peerid = "'.dbEsc($peer_id).'" AND ip = "'.dbEsc($peer_ip).'" AND infohash = "'.dbEsc($infohash).'" LIMIT 1');
	exit;
}

# Set to seeder
if($event == 'completed'){ // Pretty much useless
	$isseeder = 1; // Already 1 since $_GET['left'] would be 0
}

# Check if peer/infohash combination already exists
$res = mysql_query('SELECT COUNT(*) FROM peers WHERE peerid = "'.dbEsc($peer_id).'" AND ip = "'.dbEsc($peer_ip).'" AND infohash = "'.dbEsc($infohash).'"');

if(mysql_result($res, 0, 0) == 0){
	# Create new record
	mysql_query('INSERT INTO peers (infohash, peerid, ip, port, seeder, lastcontact) VALUES ("'.dbEsc($infohash).'", "'.dbEsc($peer_id).'", "'.dbEsc($peer_ip).'", '.dbEsc($peer_port).', '.$isseeder.', '.time().')');
}
else{
	# Update record
	mysql_query('UPDATE peers SET lastcontact = '.time().' WHERE peerid = "'.dbEsc($peer_id).'" AND ip = "'.dbEsc($peer_ip).'" AND infohash = "'.dbEsc($infohash).'"');
}

# Retrieve available peers - Select random peers if necessary
if($scrape_peers <= $max_peers){
	$res = mysql_query('SELECT peerid, ip, port FROM peers WHERE infohash = "'.dbEsc($infohash).'" AND ip != "'.dbEsc($peer_ip).'" AND lastcontact > '.(time()-$interval_idle).' LIMIT '.$max_peers);
}
else{
	$offset = mt_rand(0, $scrape_peers - $max_peers);
	$res = mysql_query('SELECT peerid, ip, port FROM peers WHERE infohash = "'.dbEsc($infohash).'" AND ip != "'.dbEsc($peer_ip).'" AND lastcontact > '.(time()-$interval_idle).' LIMIT '.$offset.', '.$max_peers);
}

$peerlist = array();
while($row = mysql_fetch_array($res)){
	//$peer = array();
	//$peer['id'] = $row['peerid'];
	//$peer['ip'] = $row['ip'];
	//$peer['port'] = (int) $row['port'];
	$peerdict = new bencodeDict(array(
		'id' => $row['peerid'],
		'ip' => $row['ip'],
		'port' => ((int) $row['port'])
	));
	array_push($peerlist, $peerdict);
}

//$response = 'd'.bencode('complete').bencode($scrape_seeders).bencode('incomplete').bencode($scrape_leechers).bencode('interval').bencode($interval).bencode('peers').bencodeList($peerlist, true).'e';

$response = new bencodeDict(array(
	'complete' => $scrape_seeders,
	'incomplete' => $scrape_leechers,
	'interval' => $interval,
	'peers' => (new bencodeList($peerlist))
));

echo $response;

mysql_close($link);
?>