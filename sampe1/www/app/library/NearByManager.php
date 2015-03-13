<?php

class NearByManager extends Phalcon\Mvc\User\Component
{

    const MIN_RECORDS = 20;

    public function searchNearBy($account_id, $latitude, $longitude)
    {
        $result = \Positions::searchNearBy($latitude, $longitude, 4, 60);
        $map = array();
        foreach ($result as $row) {
            // 删除冗余
            unset($row['delta_lat']);
            unset($row['delta_lon']);
            $map[$row['account_id']] = $row;
        }
        // 排除自己
        if (isset($map[$account_id])) {
            unset($map[$account_id]);
        }
        //
        $size = count($map);
        // 一个人也没有
        if ($size == 0) {
            $map[$account_id] = array(
                'account_id' => $account_id,
                'latitude' => $latitude,
                'longitude' => $longitude
            );
        }
        //
        $index = 0;
        $faked = array();
        // 人数不足 添加机器人
        while ($size < static::MIN_RECORDS && $index < 10) {
            // 对每一个真实玩家 生成一个机器人
            foreach ($map as &$row) {
                if (! isset($row['status'])) {
                    // 获取在线状态
                    $row['status'] = SessionManager::isOnline($row['account_id']);
                }
                // 取经纬度个位数
                $x = intval($row['latitude']) % 10;
                $y = intval($row['longitude']) % 10;
                $id = $row['account_id'] . $x . $y . $index;
                mt_srand($id + $row['status']);
                $lat = max(min($row['latitude'] + mt_rand(- 100, 100) / 100, 90), - 90);
                $lon = max(min($row['longitude'] + mt_rand(- 100, 100) / 100, 180), - 180);
                $status = mt_rand(0, 9);
                if ($status >= 6) {
                    $status = 0;
                } elseif ($status > 2) {
                    $status = 1;
                }
                $info = $this->fakeUserInfoContainer->getUserInfo($id);
                $faked[$id] = array(
                    'account_id' => $id,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'nickname' => $info['nickname'],
                    'photo' => $info['photo'],
                    'status' => $status
                );
            }
            ++ $index;
            unset($row);
            $size = count($map) + count($faked);
        }
        
        // 排除自己
        if (isset($map[$account_id])) {
            unset($map[$account_id]);
        }
        // 返回结果
        return array_values(array_merge($map, $faked));
    }
}