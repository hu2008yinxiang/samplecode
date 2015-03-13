<?php

class Records extends \Phalcon\Mvc\Model
{

    const TYPE_OTHER = 'other';

    const TYPE_CHIP = 'chip';

    const TYPE_DIAMOND = 'diamond';

    const SRC_FACEBOOK = 2;

    const CODE_GIFT = 1;

    const REASON_DAILYGIFT = 'daily_gift';

    const SRC_SYSTEM = 1;

    const CODE_TAPJOY = 2;

    const REASON_TAPJOY = 'tapjoy_points';

    public function initialize()
    {
        Records::setup(array(
            'notNullValidations' => false
        ));
    }

    public function beforeSave()
    {
        // if(isset($this->when)){
        $this->when = time();
    }

    /**
     *
     * @param string $account_id            
     * @param string $src            
     * @param enum $type            
     * @param int $amount            
     * @param int $code            
     * @param string $reason            
     */
    public static function record($account_id, $src, $type, $amount, $code, $reason = '')
    {
        $record = new Records();
        $record->account_id = $account_id;
        $record->src = $src;
        $record->type = $type;
        $record->amount = $amount;
        $record->code = $code;
        $record->reason = $reason;
        $ret = $record->save();
        if (! $ret) {
            throw new Exception('insert failed');
        }
    }
}