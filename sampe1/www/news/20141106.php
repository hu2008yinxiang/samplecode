<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>notice</title>
<link href="css/game.css" rel="stylesheet" type="text/css" />

<?php
$fontscale = 0;
if (isset($_GET['fontscale'])) {
    $modifyFactor = $_GET['fontscale'];
} else {
    $width = 2.3622048;
    if (isset($_GET['width'])) {
        $width = $_GET['width'];
    }
    $modifyFactor = $width / 3.0;
    if ($modifyFactor > 1) {
        $remin = $modifyFactor - 1;
        $modifyFactor = 1 + $remin * 2 / 3;
    }
}

$style2FontSize = 16 * $modifyFactor;
?>

<style type="text/css">
html {
	background-color: rgb(0, 0, 0);
	height: 100%;
}

body {
	padding: 10px;
	background-color: rgb(0, 0, 0);
	-background-color: rgba(0, 0, 0, 0.1);
	height: 100%;
}

* {
	font-size: <?php echo$style2FontSize, 'px'; ?>;
	color: #ececec;
}

.highlight {
	color: rgb(0, 203, 181);
}
.STYLE2p {
	font-size: <?php echo$style2FontSize* 1.2, 'px'; ?>;
	color: rgb(0, 255, 255);
	text-align: center;
}

.STYLE3p {
	font-size: <?php echo$style2FontSize, 'px'; ?>;
	color: rgb(255, 228, 122);
}

.STYLE5 {
    border-bottom-width: thin;
    border-bottom-style: dashed;
    border-bottom-color: white;
    margin-bottom: 6px;
}

<!--
.Title {
	background: url(images/body_bg.png) no-repeat;
	font-size: 30px;
	padding: 100px 0px 30px 240px;
	margin: 10px 0px -10px 300px;
	color: yellow;
}

-->
ul.disc {
	list-style-type: disc
}

ul.circle {
	list-style-type: circle
}
</style>
</head>
<body>
	<script language="javascript" type="text/javascript">
	function resizeImg(obj) {
		var a = parent.document.getElementById("a");
		var r=a.height/a.width;
		a.width=document.body.clientWidth;
		a.height=document.body.clientWidth * r;
	
	}
	function adjustImg() {
		var a = parent.document.getElementById("a");
		var r=a.height/a.width;
		a.width=document.body.clientWidth;
		a.height=document.body.clientWidth * r;
	
	}
// 	window.onresize = adjustImg;
</script>

<?php
$resource_url = "images";
// $url=$resource_url.'/news_2_17.jpg';
$url = $resource_url . '/news.jpg';
// echo ('<img id="a" src="' . $url . '" width="100%"/>');
?>
<!--<img id="a" src="http://static.das.tdgame.biz/resource/News/news_10-14.jpg" width="100%"/>-->
	<div>

		<!--打折-->
		<div class="STYLE5">
			<div>
				<div class="STYLE2p">New Table Gifts!</div>
			</div>
			<div>
				<p class="STYLE3p">The completely new table gifts is coming. rock
					your desk mates NOW! <!-- <font color="#ff6c00">Extra crystals</font> will be sent to your
					mailbox.
					 -->
				</p>
			</div>
		</div>
		<div class="STYLE5">
			<div>
				<div class="STYLE2p">Compete Around the World!</div>
			</div>
			<div>
				<p class="STYLE3p">Global rank is ready, can you find your order?
					No? Don't be discouraged, your trying will be rewarded at the
					Sunday 9:00 PM. It's never to be too late to climb up the Rank
					List. <!-- <font color="#ff6c00">Extra crystals</font> will be sent to your
					mailbox.
					 -->
				</p>
			</div>
		</div>
		<div class="STYLE5">
			<div>
				<div class="STYLE2p">Free Lottery Upgraded!</div>
			</div>
			<div>
				<p class="STYLE3p">You will get x10 chips when you play free lottery.<!-- <font color="#ff6c00">Extra crystals</font> will be sent to your
					mailbox.
					 -->
				</p>
			</div>
		</div>
	</div>
</body>
</html>



<!--  
   <li><div><span class="STYLE2"><img src="images/dot.png"> Event dungeon（today only）</span></div></li> 
 -->

