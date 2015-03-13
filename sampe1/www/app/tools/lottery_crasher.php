<?php
$req_data = array();
$req_data['account_id'] = 100433753;
$req_data['role'] = 'mina';
$req_data['cmds'] = array();
$req_data['cmds'][] = array(
    'cmd' => 'PlayLottery',
    'diamond' => 10
);

$count = 10000;
$context = stream_context_create(array(
    'http' => array(
        'method' => 'POST',
        'content' => json_encode($req_data),
        'ignore_errors' => true
    )
));
$errors = 0;
$map = array(
    500 => 0,
    1000 => 0,
    1500 => 0,
    2000 => 0,
    2500 => 0,
    3000 => 0,
    3500 => 0,
    4000 => 0
);

while($times-- > 0){
    
}
