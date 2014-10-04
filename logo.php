<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
<title>eyepat.ch tracker</title>
<link rel="icon" type="image/png" href="http://eyepat.ch/favicon.png">

<style type="text/css">
body{
	background-color: #000000;
}

div#cubic{
	background-color: #000000;
	background-image: url('biglogo.png');
	background-repeat: no-repeat;
	background-position: top left;

	position: absolute;
	top: 50%;
	left: 50%;
	width: 800px;
	height: 600px;
	margin-left: -400px;
	margin-top: -300px;
	
	-moz-user-select: none;
	-khtml-user-select: none;
	user-select: none;
	cursor: default;
	
	padding: 0px;
}

img.digit{
	width: 31px;
	height: 46px;
	margin: 0px;
	background-color: #D6D6D6;
	position: relative;
	left: 423px;
	top: 400px;
}

div.digit{
	width: 31px;
	height: 46px;
	margin: 0px;
	padding: 0px;
	position: relative;
	left: 423px;
	top: 400px;
	background-color: #D6D6D6;
	background-repeat: no-repeat;
	background-position: top left;
	display: inline-block;
	zoom: 1;
	*display: inline;
}
div.digit.n0{
	background-image: url('digits/0.png');
}
div.digit.n1{
	background-image: url('digits/1.png');
}
div.digit.n2{
	background-image: url('digits/2.png');
}
div.digit.n3{
	background-image: url('digits/3.png');
}
div.digit.n4{
	background-image: url('digits/4.png');
}
div.digit.n5{
	background-image: url('digits/5.png');
}
div.digit.n6{
	background-image: url('digits/6.png');
}
div.digit.n7{
	background-image: url('digits/7.png');
}
div.digit.n8{
	background-image: url('digits/8.png');
}
div.digit.n9{
	background-image: url('digits/9.png');
}

img.preload{
	display: none;
}
</style>

<script type="text/javascript" src="XHR.js"></script>
<script type="text/javascript" src="json2.js"></script>

<script type="text/javascript">
swapped = false;
function swapBackground(){
	document.getElementById('cubic').style.backgroundImage = "url('stats.png')";
	swapped = true;
}

function getStats(){
	xhr.get('stats.php', 'hasStats');
}

function hasStats(res){
	if(!swapped){
		swapBackground();
	}
	json = JSON.parse(res.responseText);
	showNumber(json.peers, 'stats_peers');
		
	window.setTimeout(getStats, 10000);
}

function showNumber(num, id){
	num = num.toString();
	piece = "";
	for(i = 0; i < num.length; i += 1){
		j = num.charAt(i);
		//piece += '<img src="digits/'+j+'.png" class="digit">';
		piece += '<div class="digit n'+j+'">&nbsp;</div>';
	}
	document.getElementById(id).innerHTML = piece;
}

window.onload = function(){
	xhr = new XHR();
	if(xhr){
		window.setTimeout(getStats, 3000);
	}
}
</script>

</head>

<body>

<div id="cubic">

<span id="stats_peers"></span>

</div>

<?php
for($i = 0; $i < 10; $i ++){
	echo '<img src="digits/'.$i.'.png" class="preload">';
}
?>
<img src="stats.png" class="preload">

</body>

</html>