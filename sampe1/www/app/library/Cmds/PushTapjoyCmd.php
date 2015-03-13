<?php
namespace Cmds;

class PushTapjoyCmd extends Cmd
{

    const CMD_NAME = 'PushTapjoy';

    const FORMAT = 'trans_raw_%s_%s';

    public static function defaultData()
    {
        return array(
            'trans' => '',
            'id' => '',
            'points' => 0
        );
    }

    protected function do_execute()
    {
        $delta_chip = 0;
        $ua = $this->getMe();
        do {
            $id = $this->data['id'];
            $points = $this->data['points'];
            $raw_trans = sprintf(static::FORMAT, $id, $points);
            $encoded_trans = md5($raw_trans);
            $trans = $this->data['trans'];
            if (empty($trans) || $trans != $encoded_trans) {
                //$this->ret['result']['errno'] = \Errors::OP_DENIED;
                break;
            }
            
            $point = \TapjoyPoints::findFirst(array(
                'account_id = :account_id: AND trans = :trans:',
                'bind' => array(
                    'account_id' => $this->account_id,
                    'trans' => $id
                )
            ));
            
            if ($point) {
                break;
            }
            
            $point = new \TapjoyPoints();
            $point->account_id = $this->account_id;
            $point->trans = $id;
            $point->points = $points;
            $point->when = date('Y-m-d H:i:s', $id);
            
            $delta_chip = 0;
            if ($point->save()) {
                $delta_chip = $point->points;
            }
            
            $ua->chip += $delta_chip;
            \Records::record($this->account_id, \Records::SRC_SYSTEM, \Records::TYPE_CHIP, $delta_chip, \Records::CODE_TAPJOY, \Records::REASON_TAPJOY);
            $ua->save();
        } while(false);
        
        $this->ret['result']['chip'] = $ua->chip;
        $this->ret['result']['diamond'] = $ua->diamond;
        $this->ret['result']['delta_chip'] = $delta_chip;
    }
}