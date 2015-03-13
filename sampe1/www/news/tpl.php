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
.STYLE2p {
	font-size: <?php echo ($style2FontSize);?>px;
	color: rgb(0, 255, 255);
	margin: 10px 0px 10px 0px;
}

.STYLE3p {
	font-size: <?php echo ($style2FontSize);?>px;
	color: rgb(255, 228, 122);
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
echo ('<img id="a" src="' . $url . '" width="100%"/>');
?>
<!--<img id="a" src="http://static.das.tdgame.biz/resource/News/news_10-14.jpg" width="100%"/>-->
	<div>

		<!--打折-->
		<div class="STYLE5">
			<div>
				<span class="STYLE2p">Crystals on sale! (Now ~ Feb.23)</span>
			</div>
			<div>
				<span class="STYLE3p">Get 20% extra crystals for every purchase now!
					<font color="#ff6c00">Extra crystals</font> will be sent to your
					mailbox.
				</span>
			</div>
		</div>

		<!--神祭-->
		<!--<div class="STYLE5">-->
		<!--	<div><span class="STYLE2p">Ceremony of Deity!</span></div>-->
		<!--	<div><span class="STYLE3p">8:00 am, Dec.21~8:00 am, Dec.23, a <font color="#ff6c00">big ceremony of deity</font> is coming! Appearance chance of 5☆ heroes in Crystal Draw is up a lot and a chance for heroes which may come out at Lv.30 in Crystal Draw! 3up chance for the <font color="#ff6c00">Mighty Time Lords</font>!</span></div>-->
		<!--</div>-->

		<!--节日活动-->
		<!--	<div class="STYLE5">-->
		<!--	 	<div><span class="STYLE2p">Merry Christmas!</span></div>-->
		<!--			<div><span class="STYLE3p">8:00 am, Dec.16~8:00 am, Dec.26, New Year celebration! Merry Christmas! Great gifts are waiting for you! Prepare your sock now!</span></div>-->
		<!--			<div><span class="STYLE3p"><font color="#6be602">1.</font>8:00 am, Dec.24~8:00 am, Dec.26, Join in the crystal draw, appearance chance of <font color="#ff6c00">5☆ heroes</font> in Crystal Draw is up a lot! Take a chance to meet the <font color="#ff6c00">Christmas Elf</font>!</span></div>-->
		<!--			<div><span class="STYLE3p"><font color="#6be602">2.</font>0:00 am, Dec.19~0:00 am, Dec.25, <font color="#ff6c00">100 crystals</font> will be rewarded for login everyday!</span></div>-->
		<!--			<div><span class="STYLE3p"><font color="#6be602">3.</font>Dec.16~Dec.26, Santa Claus is coming in Santa Claus Befalls, keep him and his buddies now!</span></div>-->
		<!--			<div><span class="STYLE3p"><font color="#6be602">4.</font>4:00 am~5:00 am and 4:00 pm~5:00 pm, Christmas Gift is sent out in The Gift of The Magi! Guess what gifts Magi brings?</span></div>	-->
		<!--			<div><span class="STYLE3p"><font color="#6be602">5.</font>×2 Appearance chance for Flask!</span></div>-->
		<!--			<div><span class="STYLE3p"><font color="#6be602">6.</font>Rare at Friendly Draw!</span></div>-->
		<!--			<div><span class="STYLE3p"><font color="#6be602">7.</font>×2 Skill Up rate!</span></div>-->
		<!--	</div>-->

		<!--进化后的★6也有机会直接抽到！并且能抽到期间限定的xx-->
		<!--A chance to get an evolved 6☆ hero in Crystal Draw! And also XX which is only offered for the event!-->

		<!--版本更新-->
		<!--	<div class="STYLE5">-->
		<!--	    <div><span class="STYLE2p">Version Update!</span></div>-->
		<!--	    <div><span class="STYLE3p">New heroes <font color="#ff6c00">"Kings and Heroes"</font> are coming on stage! New combination, new challenge! <font color="#ff6c00">Classical heroes</font>, divine 5☆ heroes show up in Crystal Draw! Don't hesitate, Come and Get It! Coming soon! Estimated Time: am 6:00~am 8:00, Dec.5.</span></div>-->
		<!--	</div>-->

		<!--新卡-->
		<!--<div class="STYLE5">-->
		<!--	<div><span class="STYLE2p">Heroes Descended!</span></div>-->
		<!--	<div><span class="STYLE3p">Big revision of DAS on 8:00 am, Dec.13! <font color="#ff6c00">New heroes</font> are coming on stage! <font color="#ff6c00">Great kings</font>, divine 5☆ heroes show up in Crystal Draw! Don't hesitate, Come and Get It!</span></div>-->
		<!--</div>	-->



		<!--传奇生物-->
		<!--	<div><span class="STYLE3p">8:00 am, Dec.7~8:00 am, Dec.9, a <font color="#ff6c00">big ceremony of deity</font> is coming! A chance for heroes which may come out at Lv. 30 in Crystal Draw! 3up chance for  <font color="#ff6c00">Legendary Sacred Beasts</font>!</span></div>-->



		<!--普通周活动-->
		<div class="STYLE5">
			<div>
				<span class="STYLE2p">Dragon & Sword Weekly Carnival</span>
			</div>
			<div>
				<span class="STYLE3p"><font color="#6be602">1.</font>×2 Skill Up
					rate!</span>
			</div>
			<div>
				<span class="STYLE3p"><font color="#6be602">2.</font>×2 Drops for
					Earth Centaur!</span>
			</div>
			<div>
				<span class="STYLE3p"><font color="#6be602">3.</font>×2 Drops for
					Weekday Dungeons!</span>
			</div>
			<div>
				<span class="STYLE3p"><font color="#6be602">4.</font>×1.5 Coins for
					Dungeon of Wealth!</span>
			</div>
		</div>

		<!--	金属龙出现up-->
		<!--	<div><span class="STYLE3p"><font color="#6be602">2.</font>×2 Appearance chance for Flask!</span></div>-->
		<!--	技能升级up-->
		<!--	<div><span class="STYLE3p"><font color="#6be602">1.</font>×2 Skill Up rate!</span></div>-->
		<!--	素材关掉率*2-->
		<!--	<div><span class="STYLE3p"><font color="#6be602">2.</font>×2 Drops for Weekday Dungeons!</span></div>-->

		<!--	打钱副本*1.5-->
		<!--	<div><span class="STYLE3p"><font color="#6be602">4.</font>×1.5 Coins for Dungeon of Wealth!</span></div>-->

		<!--	友情抽出五星-->
		<!--	<div><span class="STYLE3p"><font color="#6be602">5.</font>Rare at Friendly Draw!</span></div>-->

		<!--	+1卡提升-->
		<!--	<div><span class="STYLE3p"><font color="#6be602">5.</font>×2 Drops for + cards in Dungeons!</span></div>-->

		<!--	登陆送魔石-->
		<!--	<div><span class="STYLE3p"><font color="#6be602">2.</font>0:00 am, Nov.28~0:00 am, Dec.2, <font color="#ff6c00">100 crystals</font> will be rewarded for login everyday!</span></div>-->

		<!--	水晶抽节日怪-->
		<!--	<div><span class="STYLE3p"><font color="#6be602">4.</font>7:00 am, Dec.24~3:00 pm, Dec.27, Join in the crystal draw, take a chance to meet the Christmas Elf!</span></div>-->
		<!--	节日副本，单次-->
		<!--	<div><span class="STYLE3p"><font color="#6be602">2.</font>Oct.15~Oct.31, Dullahan befalls in Sleepy Valley.</span></div>-->
		<!--	节日副本，循环-->
		<!--	<div><span class="STYLE3p"><font color="#6be602">3.</font>4:00 am~5:00 am and 4:00 pm~5:00 pm, Jack makes havoc in pumpkin finca.</span></div>-->

		<div class="STYLE5">
			<div>
				<span class="STYLE2p">Special Offer for Crystal Draw!</span>
			</div>
			<div>
				<span class="STYLE3p">At least a 5-star card is guaranteed in "10
					cards" Crystal Draw! Only 4500 crystals needed. Don't Hesitate!</span>
			</div>
		</div>

		<!--抽卡活动，如果开始日期1号，则截止日期7号，相差6-->
		<!--循环是火水木光暗-->
		<!--		<div class="STYLE5">-->
		<!--		 	<div><span class="STYLE2p">Fire-type heroes UP!</span></div>-->
		<!--		   	<div><span class="STYLE3p">During this week, the chance for fire-type heroes in Crystals Draw is 2 times up, including 5☆ Fire-type heroes.</span></div>-->
		<!--		</div>-->

		<div class="STYLE5">
			<div>
				<span class="STYLE2p">Water-type heroes UP!</span>
			</div>
			<div>
				<span class="STYLE3p">During this week, the chance for Water-type
					heroes in Crystals Draw is 2 times up, including 5☆ Water-type
					heroes.</span>
			</div>
		</div>

		<!--	<div class="STYLE5">-->
		<!--	 	<div><span class="STYLE2p">Wood-type heroes UP!</span></div>-->
		<!--	   	<div><span class="STYLE3p">During this week, the chance for Wood-type heroes, which may come out at Lv.30, in Crystals draw is 2 times up, including 5☆ Wood-type heroes.</span></div>-->
		<!--	</div>-->

		<!--	 <div class="STYLE5">-->
		<!--         <div><span class="STYLE2p">Light-type heroes UP!</span></div>-->
		<!--         <div><span class="STYLE3p">During this week, the chance for Light-type heroes in Crystals Draw is 2 times up, including 5☆ Light-type heroes.</span></div>-->
		<!--     </div>-->

		<!--	 <div class="STYLE5">-->
		<!--         <div><span class="STYLE2p">Dark-type heroes UP!</span></div>-->
		<!--         <div><span class="STYLE3p">During this week, the chance for Dark-type heroes in Crystals Draw is 2 times up, including 5☆ Dark-type heroes.</span></div>-->
		<!--     </div>-->

		<!--  	<div class="STYLE5">-->
		<!--  			<div><span class="STYLE2p">Earth Centaur befalls.</span></div>-->
		<!--      		<div><span class="STYLE3p">New special dungeon - Earth Centaur is open for two weeks.</span></div>-->
		<!--	</div>-->

		<div class="STYLE5">
			<div>
				<span class="STYLE2p">Fire Dragon befalls!</span>
			</div>
			<div>
				<span class="STYLE3p">New special dungeon - Dragon of Fire is open
					for two weeks.</span>
			</div>
		</div>

		<ul class="STYLE5">
			<li><span class="STYLE2p">Double crystals for the first charge!</span></li>
			<li><span class="STYLE3p">All the new users get double crystals for
					the first charge!(exclude monthly offer).</span></li>
		</ul>


		<div class="STYLE5">
			<div>
				<span class="STYLE2p">Monthly offer arrives, rewarding crystals
					everyday!</span>
			</div>
			<div>
				<span class="STYLE3p">The user who purchases a monthly offer will
					get 100 crystals as a daily reward for 30 days.</span>
			</div>
		</div>

		<div class="STYLE5">
			<div>
				<span class="STYLE2p">Special Event in normal dungeons!</span>
			</div>
			<div>
				<span class="STYLE3p">Special events for normal dungeons! Exp * 1.5,
					Drop * 2, Stamina cost * 0.5 ... Start the adventure now!</span>
			</div>
		</div>


		<div class="STYLE5">
			<div>
				<span class="STYLE2p">Special Dungeon - Dungeon of Alchemy!</span>
			</div>
			<div>
				<span class="STYLE3p">Open in 3:00-4:00 and 15:00-16:00 everyday.
					You'll get rare enhance materials in these dungeons.</span>
			</div>
		</div>


		<div class="STYLE5">
			<div>
				<span class="STYLE3p">The above-mentioned date &amp; time are of UTC time.</span>
			</div>
		</div>

		<!-- 	
		<ul style="margin: 0 0 20px 20px; padding: 0px;">
		
			<div><span class="STYLE3">login bonus：1 free magic stone per day（delivered as late as 4AM the following day)</span></div>
			
			<li style="color: #033fb8;"><font color="#764f00" style="font-size: 12px;">New Treasure Dungeons available! Get more cool equipment here:
			<br>a. Treasure (Easy): equipment from Blue Star to Purple Star;
			<br>b. Treasure (Normal): equipment from Blue Star to Orange;
			<br>c. Treasure (Hard): equipment from Blue Star to Orange Star.
			</font></li>
			<li style="color: #033fb8;"><font color="#764f00" style="font-size: 12px;">Charts by level: players of every 10 levels share a chart. Beat your match!</font></li>
			<li style="color: #033fb8;"><font color="#764f00" style="font-size: 12px;">Raised upper level of equipment to '.sprintf($bold, '120').'.</font></li>
			<li style="color: #033fb8;"><font color="#764f00" style="font-size: 12px;">New checkin rule of guild war:
			<br>When checkin starts, all offline players, who were active in last '.sprintf($bold, '24 hours').', will check in automatically, and online players be noticed with a popup.
			</font></li>
		</ul>
-->
	</div>
</body>
</html>



<!--  
   <li><div><span class="STYLE2"><img src="images/dot.png"> Event dungeon（today only）</span></div></li> 
 -->

