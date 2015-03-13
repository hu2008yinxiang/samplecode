<?php
define('APP_PATH', realpath(__DIR__ . '/../../'));
$tasks = include APP_PATH . '/app/cron/tasks.php';

$lines = array();
$new = array();
exec('crontab -l', $lines);
while (true) {
    $line = array_shift($lines);
    if (is_null($line)) {
        break;
    }
    $count = count($tasks);
    $l = array_shift($lines);
    $e = array_shift($lines);
    for ($index = 0; $index < $count; ++ $index) {
        if ($line == '# ++++' . $tasks[$index]['name']) {
            if ($e == '# ----' . $tasks[$index]['name']) {
                // 删除原来的
                $l = null;
                $e = null;
                $line = null;
                continue;
            }
        }
    }
    if (! is_null($e)) {
        array_unshift($lines, $e);
    }
    if (! is_null($l)) {
        array_unshift($lines, $l);
    }
    if (! is_null($line)) {
        array_push($new, $line);
    }
}
foreach ($tasks as $t) {
    // 安装全新
    array_push($new, '# ++++' . $t['name']);
    array_push($new, $t['line']);
    array_push($new, '# ----' . $t['name']);
}
$content = implode(PHP_EOL, $new);
$name = tempnam(sys_get_temp_dir(), 'cron');
file_put_contents($name, $content);
system('crontab ' . $name);
unlink($name);
system('crontab -l');
echo PHP_EOL;
