<?php
error_reporting(E_ALL | E_STRICT | E_NOTICE);

function get_context($current = 0, $limit = 180, $artist = 'Canada', $sort = 'port_created', $subject = 'all')
{
    $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Encoding' => ' gzip, deflate',
        'Accept-Language' => 'zh-cn,en-us;q=0.7,en;q=0.3',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
        'Content-Length' => '0',

        // 'Cookie' => '__utma=166813500.669747205.1409128660.1409284824.1411874982.3; __utmz=166813500.1411874982.3.2.utmcsr=91.213.30.151|utmccn=(referral)|utmcmd=referral|utmcct=/search; PHPSESSID=e653993d6db906b8dab082bbe45a8861; __utmb=166813500.2.10.1411874982; __utmc=166813500',
        // 'Host' => 'selflessportraits.com',
        // 'Pragma' => 'no-cache',
        'Referer' => 'http://selflessportraits.com/gallery/',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:32.0) Gecko/20100101 Firefox/32.0 FirePHP/0.7.4',
        'X-ARTIST' => $artist,
        'X-CURRENT' => $current,
        'X-FRIENDS' => 'false',
        'X-LIMIT' => $limit,
        'X-SORT' => 'port_created',
        'X-SUBJECT' => $subject,
        'x-insight' => 'activate'
    );

    $header = '';
    foreach ($headers as $k => $v) {
        $header .= ($k . ': ' . $v . "\r\n");
    }
    return stream_context_create(array(
        'http' => array(
            'method' => 'POST',
            'header' => $header
        )
    ));
}

function get_content($current = 0, $limit = 180, $artist = 'all', $sort = 'port_created', $subject = 'Canada')
{
    $context = get_context($current, $limit, $artist, $sort, $subject);
    while (true) {
        $content = file_get_contents('http://selflessportraits.com/getPortraits-v3.php', false, $context);
        file_put_contents(sprintf(__DIR__ . '/c%dl%da%ss%ss%s.json', $current, $limit, $artist, $sort, $subject), $content);
        // HTTP 错误
        if (isset($http_response_header) && strlen($content) > 0)
            return $content;
    }
}

function crop_image($content)
{
    $quality = 75;
    if (! $content)
        return false;
    $img_src = imagecreatefromstring($content);
    $info = getimagesizefromstring($content);
    $src_w = $info[0];
    $src_h = $info[1];
    $src_type = $info[2];
    $dst_width = $src_w / 2;
    $dst_height = $src_h;
    $img = imagecreatetruecolor($dst_width, $dst_height);
    // $img_r = imagecreatetruecolor($dst_width, $dst_height);
    imagecopy($img, $img_src, 0, 0, 0, 0, $dst_width, $dst_height);
    // imagecopy($img_r, $img_src, 0, 0, $dst_width, 0, $dst_width, $dst_height);
    imagedestroy($img_src);
    // imagejpeg($img_l, $out_l, 75);
    // imagejpeg($img_r, $out_r, 75);
    return $img;
}

function parse_content($str)
{
    $ret = array();
    foreach ($str as $s) {
        $data = json_decode($s, true);
        foreach ($data as $p) {
            $ret[$p['port_subject_id']] = $p['port_merged_thumb'];
        }
    }
    // file_put_contents(__DIR__ . '/images.json', json_encode($ret));
    echo count($ret), PHP_EOL;
    $contents = array();
    foreach ($ret as $k => $v) {

        // echo $k, '=>', $v, PHP_EOL;
        if (stripos(php_uname(), 'Windows') !== false) {
            while (true) {
                $lines = array();
                exec('tasklist /FI "IMAGENAME eq php.exe" /NH', $lines);
                if (count($lines) < 20)
                    break;
                sleep(2);
            }

            $command = 'D:\webdev\php-5.6.2-nts-Win32-VC11-x86\php.exe "' . __FILE__ . '" "' . escapeshellarg($k) . '" "' . escapeshellarg($v) . '"';
            pclose(popen("start /B " . $command, "r"));
        } else {
            while (true) {
                $line = system('ps aux | grep ' . __FILE__ . ' | wc -l');
                if ($line < 21)
                    break;
                sleep(2);
            }
            $command = 'php "' . __FILE__ . '" "' . $k . '" "' . $v . '"';
            exec($command . '< /dev/null > /dev/null &');
        }
    }

    return;
    ?>

<table>
	<tr>
		<th>ID</th>
		<th>Picture</th>
	</tr>
  <?php

    foreach ($data as $p) {
        // $p['port_merged_thumb'] 合并后小图
        // $p['port_merged] 合并后大图
        // $p['port_original'] 画的原图
        // $p['port_linked_profile_pic] // 原图
        //
        ?>
  <tr>
		<td><?php echo $p['port_subject_id'];?></td>
		<td><img
			src="http://selflessportraits.com/<?php echo $p['port_merged_thumb'];?>" /></td>
	</tr>
  <?php }?>
</table>

<?php
}

function worker($k, $v)
{
    $file_name = __DIR__ . '/iphotos/pics/' . $k . '.jpg';
    if (is_file($file_name))
        return;
    $index = 0;
    while (true) {
        ++ $index;
        if ($index > 10)
            return;
        $v = file_get_contents('http://selflessportraits.com/' . $v);
        // $headers = $http_response_header;
        if (strlen($v) == 0)
            continue;
        if (! isset($http_response_header) || empty($http_response_header)) {
            // HTTP 失败
            continue;
        }
        //
        $len = 0;
        foreach ($http_response_header as $h) {
            $match = sscanf($h, 'Content-Length: %d');
            if ($match[0] != null) {
                $len = $match[0];
                break;
            }
        }
        if ($len != strlen($v)) {
            echo 'File Corrupted!', PHP_EOL;
            continue;
        }

        $img = crop_image($v);
        imagejpeg($img, $file_name, 75);
        return;
    }
}
switch ($argc) {
    case 3:
        worker($argv[1], $argv[2]);
        break;
    default:
        $max = 1000;
        $index = 0;
        // $cs = array();

        while (true) {
            if ($index > $max)
                break;
            $cs = get_content($index, 180, 'all', 'port_created', 'all');
            parse_content(array(
                $cs
            ));
            ++ $index;
        }
        // parse_content($cs);
        break;
}
