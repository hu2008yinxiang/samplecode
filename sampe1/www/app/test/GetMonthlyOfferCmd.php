<?php
return;
$raw_data['role'] = 'mina';
$raw_data['account_id'] = '100433753';

if (false) {
    $monthlyOffer = array(
        'iapid' => 'monthly_offer',
        'perday' => 600000,
        'amount' => 4000000,
        'price' => 4.99
    );
    $endDate = date_create('30 days 23:59:59');
    $currentDate = date_create('yesterday');
    $value = array(
        'end' => $endDate,
        'current' => $currentDate,
        'perday' => $monthlyOffer['perday']
    );
    $extra = \Extras::load($raw_data['account_id'], \Extras::MONTHLY_OFFER, $value);
    $extra->value = $value;
    $extra->save();
    return;
}

$raw_data['cmds'] = array();
$raw_data['cmds'][] = array(
    'cmd' => \Cmds\GetMonthlyOfferCmd::CMD_NAME
);