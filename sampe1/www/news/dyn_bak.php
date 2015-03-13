<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>notice</title>

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
$style2FontSize = 12;
?>

<style type="text/css">
@font-face {
	font-family: appfont;
	src: url(Typeface/impact.woff);
}

html, body, *, ul {
	margin: 0;
	padding: 0;
	font-family: appfont, Impact, Arial, Helvetica, sans-serif;
}

html, body {
	height: 100%;
}

body {
	height: 100%;
}

html {
	background-color: rgb(0, 0, 0);
	height: 100%;
}

body {
	padding: 0px;
	background-color: rgb(0, 0, 0);
	-background-color: rgba(0, 0, 0, 0.1);
	height: 100%;
	overflow: visible;
}

* {
	font-size: <?php echo$style2FontSize, 'pt'; ?>;
	color: #ececec;
}

ol>li {
	list-style-position: inside;
	list-style-type: decimal;
}

.highlight {
	color: rgb(253, 229, 0);
}

.STYLE2p {
	font-size: <?php echo$style2FontSize* 1.2, 'pt'; ?>;
	color: rgb(253, 229, 0);
	text-align: center;
}

.STYLE3p {
	font-size: <?php echo$style2FontSize, 'pt'; ?>;
	color: rgb(0, 203, 181);
	margin-bottom: 6px;
	line-height: 150%;
}

.STYLE5 {
	margin-bottom: 6px;
}

div.lucky-star {
	display: inline-block;
	text-align: center;
	margin-left: 10pt;
	margin-right: 10pt;
}

div.lucky-star .icon {
	height: 65pt;
	width: 65pt;
	border-radius: 8pt;
	-webkit-border-radius: 8pt;
	-moz-border-radius: 8pt;
	min-height: 65pt;
	min-width: 65pt;
	max-height: 65pt;
	max-width: 65pt;
	box-shadow: 0 0 20pt 1pt #000 inset;
	background-size: 100% 100%;
	background-position: center;
	background-image: url('news.php/photo/0');
}

div.lucky-star .label {
	width: 65pt;
	min-width: 65pt;
	max-width: 65pt;
	overflow: hidden;
	white-space: nowrap;
	margin-top: 6pt;
}

div.lucky-star .frame {
	height: 65pt;
	width: 65pt;
	min-height: 65pt;
	min-width: 65pt;
	max-height: 65pt;
	max-width: 65pt;
	border-radius: 8pt;
	box-shadow: 0 0 16pt 2pt cyan;
}

li {
	list-style-position: inside;
	margin-bottom: 2pt;
}
</style>
</head>
<body>
	<script language="javascript" type="text/javascript">
	r = 0;
	function adjustImg() {
		var a = document.getElementById("banner");
		while(r == 0) {
			  r = a.height / a.width;
		}
		var width = document.body.clientWidth + 0;
		a.width = width;
		a.height = width * r;
	}
 	//window.onresize = adjustImg;
 	window.onload = adjustImg;
</script>

<?php

function showNews($news, $app)
{
    // $news = \News::findFirstByNewsId($id);
    if (! $news) {
        return;
    }
    $startDate = DateTime::createFromFormat('Y-m-d H:i:s', $news->start_date);
    $endDate = DateTime::createFromFormat('Y-m-d H:i:s', $news->end_date);
    switch ($news->type) {
        case News::TYPE_CHIP_SENDER:
            ?>
            <li class="STYLE3p">From <?php echo $startDate->format('m/d');?> to <?php echo $endDate->format('m/d');?>, 3 players will win <span
		class="highlight">10M chips</span> every day! Send as many free chips
		as possible to your friends to join the lucky draw! More free chips
		sent, more opportunities get!
	</li>
            <?php
            break;
        case News::TYPE_EXTRA_LOGIN_BONUS:
            ?>
    <li class="STYLE3p">From <?php echo $startDate->format('m/d');?> to <?php echo $endDate->format('m/d');?>, you can get extra login bonus! <br>&nbsp;&nbsp;&nbsp;&nbsp;<span
		class="highlight">10000 chips</span> for weekday! <br>&nbsp;&nbsp;&nbsp;&nbsp;<span
		class="highlight">30000 chips</span> for weekend!
	</li>
    <?php
            break;
        case News::TYPE_EXTRA_TASK_BONUS_4_OF_A_KIND:
            ?>
                <li class="STYLE3p">From <?php echo $startDate->format('m/d');?> to <?php echo $endDate->format('m/d');?>, <span
		class="highlight">50K</span> reward for Four of A Kind! Please check
		in daily task.
	</li>
                <?php
            break;
        case News::TYPE_EXTRA_TASK_BONUS_STAIGHT_FLUSH:
            ?>
                            <li class="STYLE3p">From <?php echo $startDate->format('m/d');?> to <?php echo $endDate->format('m/d');?>, <span
		class="highlight">250K</span> reward for Straight Flush! Please check
		in daily task.
	</li>
                            <?php
            break;
        case News::TYPE_EXTRA_TASK_BONUS_ROYAL_STAIGHT_FLUSH:
            ?>
                                        <li class="STYLE3p">From <?php echo $startDate->format('m/d');?> to <?php echo $endDate->format('m/d');?>,  <span
		class="highlight">5M</span> reward for Royal Straight Flush! Please
		check in daily task.
	</li>
                                        <?php
            break;
        case News::TYPE_FESTIVAL:
            ?>
                                                    <li class="STYLE3p">From <?php echo $startDate->format('m/d');?> to <?php echo $endDate->format('m/d');?>,  to celebrate <span
		class="highlight"><?php echo $news->title;?></span>, each player gets
		a <span class="highlight"><?php echo intval($news->content * 100 - 100), '%';?> extra offer</span>
		every day.
	</li>
                                                    <?php
            break;
        case News::TYPE_SPECIAL_OFFER:
            ?>
                                                                <li
		class="STYLE3p">From <?php echo $startDate->format('m/d');?> to <?php echo $endDate->format('m/d');?>, each player gets a <span
		class="highlight">100% extra offer</span> every day.
	</li>
                                                                <?php
            break;
        default:
            break;
    }
}
?>
<img id="banner" alt="Banner" src="images/banner.jpg">
	<div style="padding-left: 6px; padding-right: 6px;">
		<div class="STYLE5">
			<div>
				<div class="STYLE2p">Welcome to GameYep Poker!</div>
			</div>
			<div>
				<p class="STYLE3p">To celebrate our first launch on Google Play, the
					following events are ongoing:</p>
			</div>
		</div>
		<div>
			<ol>
				<?php
    
    $ids = $app->request->get('ids', null, array());
    foreach ($ids as $id) {
        ?>
				    <?php
        showNews(News::findFirstByNewsId($id), $app);
        ?>

        <?php
    }
    ?>
            <?php
            do {
                $ids = $app->newsManager->getLuckySender();
                if (! $ids) {
                    break;
                }
                $users = array();
                foreach ($ids as $id) {
                    $ua = UserAccounts::findFirstByAccountId($id);
                    if ($ua) {
                        $users[] = $ua->getInfoArray();
                        continue;
                    }
                    $users[] = $app->fakeUserInfoContainer->getUserInfo($id);
                }
                ?>
            <li class="STYLE3p">&nbsp;
					<div class="STYLE2p" style="margin-top: -8px;">EACH OF YOU WON 10M
						CHIPS!</div>
					<div
						style="text-align: center; margin-top: 8px; margin-bottom: 6px;">
		<?php
                
                foreach ($users as $u) {
                    $photoUrl = 'news.php/photo/' . $u['account_id'];
                    if ($u['photo'] > 0) {
                        $photoUrl = 'photos/' . $u['photo'] . '.jpg';
                    }
                    ?>
            <div class="lucky-star">
							<div class="frame">
								<div class="icon" style="background-image: url('<?php echo $photoUrl?>');">
								</div>
							</div>
							<div class="label highlight"><?php echo $u['nickname'];?></div>
						</div>
		    <?php
                }
                ?>
                </div>
					<div class="STYLE2p" style="margin-top: -8px;">SEND FREE CHIPS TO
						JOIN NOW!</div>
				</li><?php
            } while (0);
            ?>
			</ol>
		</div>
	</div>
</body>
</html>
