<?php
$url = 'http://image.baidu.com/i';
$dir = 'G:/dd';
mkdir($dir, 0777, true);
$params = array(
    'tn' => 'resultjsonavatarnew',
    'ie' => 'utf-8',
    'word' => '欧美女生头像',
    'cg' => 'head',
    'pn' => 0,
    'rn' => 60,
    'itg' => 0,
    'z' => '',
    'fr' => '',
    'width' => '',
    'height' => '',
    'lm' => - 1,
    'ic' => 0,
    's' => 0,
    'st' => - 1
);
$referer = 'http://image.baidu.com/i?' . http_build_query(array_merge(array(
    'tn' => 'baiduimage',
    'ipn' => 'r',
    'ct' => '201326592',
    'cl' => 2,
    'lm' => - 1,
    'sf' => - 1,
    'fmq' => '1424930363314_R'
), $params));
$context = stream_context_create(array(
    'http' => array(
        'timeout' => 15,
        'method' => 'GET',
        'header' => 'User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64; rv:32.0) Gecko/20100101 Firefox/32.0 FirePHP/0.7.4\r\n' . 'Referer: ' . $referer . '\r\n'
    )
));
$p = 0;
while (true) {
    $params['pn'] = $p * 120;
    $params['rn'] = 120;
    $target = $url . '?' . http_build_query($params);
    // echo $target, ' ';
    ++ $p;
    
    $content = json_decode(file_get_contents($target, false, $context), true);
    
    $size = count($content['imgs']);
    echo $size, PHP_EOL;
    if ($size < 1) {
        break;
    }
    foreach ($content['imgs'] as $img) {
        $filename = $dir . '/' . md5($img['objURL']) . '.jpg';
        if (is_file($filename)) {
            continue;
        }
        $img_content = false;
        while (true) {
            $img_content = file_get_contents($img['objURL'], false, $context);
            if ($img_content) {
                break;
            }
            $img_content = file_get_contents($img['thumbURL'], false, $context);
            if ($img_content) {
                break;
            }
            break;
        }
        // echo $img['objURL'], PHP_EOL;
        if ($img_content === false) {
            var_export($img);
            continue;
        }
        file_put_contents($filename, $img_content);
    }
}
