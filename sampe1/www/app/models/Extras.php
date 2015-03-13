<?php

/**
 * 用户的附加信息
 * @author 继续
 *
 */
class Extras extends \Phalcon\Mvc\Model
{

    const LAST_BIGBLIND = 'last_bigblind';

    const LAST_MINA = 'last_mina';

    const LAST_LOTTERY = 'last_lottery';

    const LIVING_PAY = 'living_pay';

    const LAST_SOT_ROUND = 'last_sot_round';

    const LAST_LOGIN_REWARD = 'last_login_reward';

    const BUYIN_COUNTER = 'buyin_counter';

    const GIFT_SUM = 'gift_sum';

    const EXTRA_THRESHOLD = 'extra_threshold';
    
    const MONTHLY_OFFER = 'monthly_offer';

    public function initialize()
    {
        static::setup(array(
            'notNullValidations' => false
        ));
    }

    public function afterFetch()
    {
        if (isset($this->value) && is_string($this->value)) {
            $val = @unserialize($this->value);
            if ($val == false && $this->value != 'false') {
                // skip
                return;
            }
            $this->value = $val;
        }
    }

    public function beforeSave()
    {
        if (isset($this->value)) {
            $this->value = serialize($this->value);
        }
    }

    public function afterSave()
    {
        $this->afterFetch();
    }

    public static function load($who, $key, $default)
    {
        static $cache = array();
        $_key = $who . ':' . $key;
        if (isset($cache[$_key])) {
            $extra = $cache[$_key];
            return $extra;
        }
        $extra = static::findFirst(array(
            'account_id = :who: AND name = :name:',
            'bind' => array(
                'who' => $who,
                'name' => $key
            )
        ));
        if (! $extra) {
            $extra = new static();
            $extra->account_id = $who;
            $extra->name = $key;
            $extra->value = $default;
            $extra->save();
        }
        
        if (is_array($default)) {
            if (! is_array($extra->value)) {
                $extra->value = $default;
            } else {
                $extra->value = array_merge($default, $extra->value);
            }
        } else {
            if (is_array($extra->value)) {
                $extra->value = $default;
            }
        }
        $cache[$_key] = $extra;
        
        return $extra;
    }
}