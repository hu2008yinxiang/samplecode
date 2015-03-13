<?php

class Friends extends Phalcon\Mvc\Model
{

    const STATUS_ADDED = 'added';

    const STATUS_REQUESTED = 'requested';

    const STATUS_REJECTED = 'rejected';

    const STATUS_DELETED = 'deleted';

    const STATUS_NONE = 'none';

    public function initialize()
    {
        $this->hasOne('src_id', 'UserAccounts', 'account_id', array(
            'alias' => 'Src'
        ));
        $this->hasOne('dst_id', 'UserAccounts', 'account_id', array(
            'alias' => 'Dst'
        ));
    }

    public static function add($src_id, $dst_id, $type)
    {
        $friend = self::findFirst(array(
            'src_id = :src_id: and dst_id = :dst_id:',
            'bind' => array(
                'src_id' => $src_id,
                'dst_id' => $dst_id
            )
        ));
        if (! $friend) {
            $friend = new Friends();
            $friend->save(array(
                'src_id' => $src_id,
                'dst_id' => $dst_id,
                'src' => $type,
                'status' => Friends::STATUS_ADDED,
                'when' => time(),
                'last_gift_day' => '1000-01-01'
            ));
        }
        // 修改类型
        if ($friend->src != 'local') { // 如果不是 local 则可以修改其src 否则不允许修改
            $friend->src = $type;
        }
        $friend->save(array(
            'status' => static::STATUS_ADDED
        ));
        return $friend;
    }

    public static function updateStatus($src_id, $dst_id, $type, $data = array())
    {
        $friend = self::findFirst(array(
            'src_id = :src_id: and dst_id = :dst_id:',
            'bind' => array(
                'src_id' => $src_id,
                'dst_id' => $dst_id
            )
        ));
        if (! $friend) {
            $friend = new Friends();
            $friend->src_id = $src_id;
            $friend->dst_id = $dst_id;
            $friend->src = $type;
        }
        foreach ($data as $k => $v) {
            $friend->$k = $v;
        }
        $friend->save();
        return $friend;
    }

    public function toArray($columns = null)
    {
        $data = parent::toArray($columns);
        $ext = array();
        foreach ($data as $k => $v) {
            switch ($k) {
                case 'src_id':
                    $ext['src_nickname'] = $this->getSrc()->nickname;
                    $ext['src_photo'] = $this->getSrc()->photo;
                    break;
                case 'dst_id':
                    $ext['dst_nickname'] = $this->getDst()->nickname;
                    $ext['dst_photo'] = $this->getDst()->photo;
                    break;
            }
        }
        foreach ($ext as $k => $v) {
            $data[$k] = $v;
        }
        return $data;
    }
}