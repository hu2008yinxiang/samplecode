<?php
define('APP_NAME', 'cron');

$config = include APP_PATH . '/app/config/config.php';
$cronTime = $config['app']['cronTime'];
$app_timezone = $config['app']['timezone'];
$sys_timezone = $config['app']['sys_timezone'];
// 得到应用结算时间
$app_zone = timezone_open($app_timezone);
$sys_zone = timezone_open($sys_timezone);
$date = new DateTime($cronTime, $app_zone);
$date->setTimezone($sys_zone);
$cronTime = $date;
$cronTime->modify('-3 minutes'); // 提前3分钟
$cronWeekday = $cronTime->format('w');
$cronHour = $cronTime->format('G');
$cronMinute = ltrim($cronTime->format('i'), '0');
$settle_rank_time = "$cronMinute $cronHour * * $cronWeekday";
$dateSeven = new DateTime('today 7:00:00', $app_zone);
$dateSeven->setTimezone($sys_zone);
$hourSeven = $dateSeven->format('G');
$hours = array();
while (count($hours) < 18) {
    $hourSeven %= 24;
    $hours[] = $hourSeven;
    ++ $hourSeven;
}
$hours_str = implode(',', $hours);

$tasks = array();
$tasks[] = array(
    'name' => 'update ranks',
    'line' => '25,55 ' . $hours_str . ' * * * php ' . APP_PATH . '/app/cli.php Ranks update'
);

$tasks[] = array(
    'name' => 'settle ranks',
    'line' => "$settle_rank_time php " . APP_PATH . "/app/cli.php Ranks settle"
);

$tasks[] = array(
    'name' => 'packing logs',
    'line' => '1 1 * * 1 php ' . APP_PATH . '/app/cli.php Log pack'
);
$date = new DateTime('23:00', $app_zone);
$date->setTimezone($sys_zone);
$hour = $date->format('G');
$tasks[] = array(
    'name' => 'lucky sender',
    'line' => '30 ' . $hour . ' * * * php ' . APP_PATH . '/app/cli.php Lucky_Sender'
);

return $tasks;