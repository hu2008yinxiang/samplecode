<?php
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Db\Column;

class Achievements extends \Phalcon\Mvc\Model
{

    public function initialize()
    {
        $this->useDynamicUpdate(true);
    }

    public function afterFetch()
    {
        //if (is_string($this->status)) {
        //    $this->status = json_decode($this->status, true);
        //}
    }

    public function beforeSave()
    {
        //if (isset($this->status) && is_array($this->status)) {
        //    $this->status = json_encode($this->status);
        //}
    }

    public function afterSave()
    {
        $this->afterFetch();
    }

    /**
     * 读取用户成就记录
     *
     * @param string $account_id
     *            用户ID
     * @param string $achievement_id
     *            成就ID
     * @param array $defaultStatus
     *            成就初始状态
     * @throws Exception 读取失败
     * @return \Achievements 成就
     */
    public static function load($account_id, $achievement_id)
    {
        $acm = static::findFirst(array(
            'account_id = :uid: AND achievement_id = :aid:',
            'bind' => array(
                'uid' => $account_id,
                'aid' => $achievement_id
            )
        ));
        if (! $acm) {
            $acm = new Achievements();
            $acm->account_id = $account_id;
            $acm->achievement_id = $achievement_id;
            $acm->status = '000';
            $acm->current = 0;
            $ret = $acm->save();
            if (! $ret) {
                foreach ($acm->getMessages() as $msg) {
                    echo $msg->getField(), ':', $msg->getMessage(), PHP_EOL;
                }
                throw new Exception('Achievement Create Failded!');
            }
        }
        return $acm;
    }
}