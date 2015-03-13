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
include 'css.php';
?>


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
<div>
<p>Fixed <span class="highlight">multi-touch</span> bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.Fixed multi-touch bug.</p>
<p>Fixed multi-touch bug.</p>
<p>Fixed multi-touch bug.</p>
<p>Fixed multi-touch bug.</p>
<p>Fixed multi-touch bug.</p>
<p>Fixed multi-touch bug.</p>
<p>Fixed multi-touch bug.</p>
<p>Fixed multi-touch bug.</p>
<p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p>
<p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p>
<p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p>
<p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p>
<p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p><p>Fixed multi-touch bug.</p>
</div>
</body>
</html>

