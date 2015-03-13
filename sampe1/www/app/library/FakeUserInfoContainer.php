<?php

class FakeUserInfoContainer extends \Phalcon\Mvc\User\Component
{

    /**
     *
     * @var array
     */
    protected $names_size = NULL;

    /**
     *
     * @var array
     */
    protected $photos_size = NULL;

    /**
     *
     * @var int
     */
    protected $suites_size = NULL;

    const SILLY_PREFIX = 'p:silly:id:';

    const SUITE_KEY_FORMAT = 'suite:%s';

    const NAME_KEY_FORMAT = 'name:%s:%s';

    const PHOTO_KEY_FORMAT = 'photo:%s:%s';

    public function setDI($dependencyInjector)
    {
        parent::setDI($dependencyInjector);
        $this->config();
    }

    protected function config()
    {
        $suites_file = APP_PATH . '/app/config/suites.php';
        $names_file = APP_PATH . '/app/config/names_map.php';
        $photos_file = APP_PATH . '/app/config/photo_map.php';
        $h_photos_file = APP_PATH . '/app/config/h_photo_map.php';
        
        $inited = false;
        $cache = $this->cache;
        if ($cache->exists(__FILE__)) {
            $inited = true;
            // 从缓存获取数据
            $data = $cache->get(__FILE__);
            $this->photos_size = $data['photos_size'];
            $this->names_size = $data['names_size'];
            $this->suites_size = $data['suites_size'];
            $this->h_photos_size = $data['h_photos_size'];
            // 文件被修改则重新初始化
            $inited = (filemtime($photos_file) == $data['photos_mtime']) && (filemtime($names_file) == $data['names_mtime']) && (filemtime($suites_file) == $data['suites_mtime']) && (filemtime($h_photos_file) == $data['h_photos_mtime']);
        }
        if ($inited) {
            return;
        }
        // 初始化数据并加入缓存
        $this->innerInit();
    }

    public function getUserInfo($account_id, $level = 1)
    {
        $cache = $this->cache;
        
        $di = $this->getDI();
        $redis = $di->get('redis');
        // 计算等级
        $llevel = 0;
        switch (strlen($account_id)) {
            case 11:
                $llevel = $account_id[9];
                $ua = \UserAccounts::findFirstByAccountId(substr($account_id, 0, 9));
                if ($ua) {
                    $rlevel = $ua->level;
                }
                break;
            case 10:
                $rlevel = $redis->hget(static::SILLY_PREFIX . substr($account_id, 0, 8), 'level');
                $llevel = $account_id[9];
                break;
            
            case 12: //
                $rlevel = $redis->hget(static::SILLY_PREFIX . $account_id, 'level');
                if (! $rlevel) {
                    $rlevel = mt_rand(3, 14);
                    $ru = \UserAccounts::findFirstByAccountId(substr($account_id, 0, 9));
                    if ($ru) {
                        $rlevel = $ru->level;
                    }
                    $redis->hset(static::SILLY_PREFIX . $account_id, 'level', $rlevel);
                    $redis->expire(static::SILLY_PREFIX . $account_id, 272800);
                }
                break;
            case 8:
            default:
                $rlevel = $redis->hget(static::SILLY_PREFIX . $account_id, 'level');
        }
        $level = $rlevel ? $rlevel : $level;
        $level += $llevel;
        
        $gender = $this->getGender($account_id);
        // 计算名字
        $name_index = $account_id % $this->names_size[$gender];
        $name_info = $cache->get(sprintf(static::NAME_KEY_FORMAT, $gender[0], $name_index));
        $name = $name_info[0] . ' ' . $name_info[1];
        // 计算头像
        $photo = mt_rand(- 10 - \UserAccounts::IN_APP_PHOTO_COUNT, \UserAccounts::IN_APP_PHOTO_COUNT);
        // 计算best suite
        $best_hand_max_index = $this->suites_size;
        if ($level > 8) {
            $best_hand_max_index = 7130;
        }
        $best_hand = ($account_id + mt_rand(0, $best_hand_max_index) + $level) % $best_hand_max_index;
        $best_hand = $cache->get(sprintf(static::SUITE_KEY_FORMAT, $best_hand));
        if ($best_hand == null) {
            $best_hand = array(
                0,
                0,
                0,
                0,
                0,
                0
            );
        }
        // 计算名字
        if ($photo > 0 && mt_rand(1, 5) > 4) {
            $name = 'Player' . mt_rand(3, 9) . mt_rand(0, 9) . mt_rand(0, 9);
        }
        mt_srand(); // 恢复随机数种子
        
        return array(
            'account_id' => $account_id,
            'nickname' => $name,
            'gender' => $gender,
            'photo' => $photo > 0 ? $photo : - 1,
            'type' => 'local',
            'chip' => 20000,
            'diamond' => 1000,
            'exp' => 0,
            'level' => $level,
            'best_hand' => $best_hand,
            'biggest_win' => 0,
            'vip_score' => 0,
            'vip_level' => 0,
            'login_last' => 0,
            'login_combo' => 0,
            'last_pay' => 0
        );
    }

    public static function isFakeUser($id)
    {
        $len = strlen($id);
        return $len == 8 || $len == 10 || $len == 11 || $len == 12;
    }

    public function getPhotoFile($account_id)
    {
        $cache = $this->cache;
        $gender = $this->getGender($account_id);
        if (true || (strlen($account_id == 12) && mt_rand(0, 9) > 3)) {
            $photo_index = $account_id % $this->h_photos_size[$gender];
            $name = $cache->get(sprintf(static::PHOTO_KEY_FORMAT, 'h:' . $gender[0], $photo_index));
        } else {
            $photo_index = $account_id % $this->photos_size[$gender];
            $name = $cache->get(sprintf(static::PHOTO_KEY_FORMAT, $gender[0], $photo_index));
        }
        $file = APP_PATH . '/app/config/iphotos/pics/' . $name;
        return $file;
    }

    protected function innerInit()
    {
        $suites_file = APP_PATH . '/app/config/suites.php';
        $names_file = APP_PATH . '/app/config/names_map.php';
        $photos_file = APP_PATH . '/app/config/photo_map.php';
        $h_photos_file = APP_PATH . '/app/config/h_photo_map.php';
        //
        $cache = $this->cache;
        $names = include $names_file;
        $photos = include $photos_file;
        $suites = include $suites_file;
        $h_photos = include $h_photos_file;
        //
        $this->suites_size = $suites['size'];
        $this->names_size = array(
            'male' => $names['male_size'],
            'female' => $names['female_size']
        );
        $this->photos_size = array(
            'male' => $photos['male_size'],
            'female' => $photos['female_size']
        );
        $this->h_photos_size = array(
            'male' => $h_photos['male_size'],
            'female' => $h_photos['female_size']
        );
        // 缓存元数据
        $cache->save(__FILE__, array(
            'suites_size' => $this->suites_size,
            'names_size' => $this->names_size,
            'photos_size' => $this->photos_size,
            'h_photos_size' => $this->h_photos_size,
            'photos_mtime' => filemtime($photos_file),
            'names_mtime' => filemtime($names_file),
            'suites_mtime' => filemtime($suites_file),
            'h_photos_mtime' => filemtime($h_photos_file)
        ), - 1);
        
        // cache suites data
        foreach ($suites['data'] as $index => $suite) {
            $key = sprintf(static::SUITE_KEY_FORMAT, $index);
            $cache->save($key, $suite, - 1);
        }
        
        $genders = array(
            'male',
            'female'
        );
        foreach ($genders as $gender) {
            // cache photos data
            foreach ($photos[$gender] as $index => $photo) {
                $key = sprintf(static::PHOTO_KEY_FORMAT, $gender[0], $index);
                $cache->save($key, $photo, - 1);
            }
            foreach ($h_photos[$gender] as $index => $photo) {
                $key = sprintf(static::PHOTO_KEY_FORMAT, 'h:' . $gender[0], $index);
                $cache->save($key, $photo, - 1);
            }
            // cache names data
            foreach ($names[$gender] as $index => $name) {
                $key = sprintf(static::NAME_KEY_FORMAT, $gender[0], $index);
                $cache->save($key, $name, - 1);
            }
        }
        //
    }

    protected function getGender($account_id)
    {
        mt_srand($account_id);
        $gender_rate = 60;
        if (strlen($account_id) == 12) {
            $gender_rate = 75;
        }
        $gender = 'female';
        if (mt_rand(1, 100) > $gender_rate) {
            $gender = 'male';
        }
        return $gender;
    }
}