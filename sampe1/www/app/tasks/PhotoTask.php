<?php

class PhotoTask extends Phalcon\CLI\Task
{

    public function mainAction($params = array())
    {}

    public function refreshAllAction($params = array())
    {
        $users = UserAccounts::find(array(
            'type <> :type:',
            'bind' => array(
                'type' => 'local'
            )
        ));
        // 有需要刷新的
        if (! $users)
            return;
        
        $photoManager = $this->di->get('photoManager');
        foreach ($users as $user) {
            // 是否需要刷新
            if (! $photoManager->needRefresh($user->account_id . '.jpg')) {
                continue;
            }
            $bindService = $this->di->get($user->type . 'Adapter');
            $token = $user->bind_token;
            $bindAccount = $bindService->bind($token);
            if (! $bindAccount || $bindAccount->bindStatus() != \Bind\BindAccount::BIND_OK) {
                continue;
            }
            // 账户绑定状态正常
            $url = $bindAccount->getPhoto();
            if (! $url) {
                continue;
            }
            // 刷新
            $this->refreshAction(array(
                $user->account_id,
                $url
            ));
        }
    }

    public function refreshAction($params = array())
    {
        $account_id = $params[0];
        $url = $params[1];
        $count = 3;
        $photoManager = $this->di->get('photoManager');
        $ret = false;
        do {
            -- $count;
            $ret = $photoManager->download($url, $account_id);
            if ($ret) {
                return true;
            }
        } while ($count > 0);
        return false;
    }

    public function corpAction(array $params = array())
    {
        $src = $params[0];
        $dst = $params[1];
        @mkdir($src, 0777, true);
        @mkdir($dst, 0777, true);
        $src = realpath($src);
        $dst = realpath($dst);
        if ($src == false || $dst == false) {
            exit('source dir or destination dir not accessable!');
        }
        echo $src, ' ==> ', $dst, PHP_EOL;
        $pattern = $src . '/*.jpg';
        $count = 0;
        $format = '%s/%010d_%s.jpg';
        foreach (glob($pattern) as $name) {
            $out_l = sprintf($format, $dst, $count, 'left');
            $out_r = sprintf($format, $dst, $count, 'right');
            $file = $name;
            echo 'Find ', $file, PHP_EOL;
            if (! is_file($file)) {
                continue;
            }
            echo 'corpping ...', PHP_EOL;
            echo $file, PHP_EOL;
            echo '-', $out_l, PHP_EOL;
            echo '-', $out_r, PHP_EOL;
            if (static::do_corp($file, $out_l, $out_r)) {
                $count ++;
                echo '+', 'SUCCESS!', PHP_EOL;
            } else {
                echo '+', 'FAILED!', PHP_EOL;
            }
            echo PHP_EOL;
        }
        echo $count, ' images corpped!', PHP_EOL;
    }

    public static function do_corp($src, $out_l, $out_r)
    {
        $quality = 75;
        $content = file_get_contents($src);
        if (! $content)
            return false;
        $img_src = imagecreatefromstring($content);
        $info = getimagesizefromstring($content);
        $src_w = $info[0];
        $src_h = $info[1];
        $src_type = $info[2];
        $dst_width = $src_w / 2;
        $dst_height = $src_h;
        $img_l = imagecreatetruecolor($dst_width, $dst_height);
        $img_r = imagecreatetruecolor($dst_width, $dst_height);
        imagecopy($img_l, $img_src, 0, 0, 0, 0, $dst_width, $dst_height);
        imagecopy($img_r, $img_src, 0, 0, $dst_width, 0, $dst_width, $dst_height);
        imagedestroy($img_src);
        // imagejpeg($img_l, $out_l, 75);
        // imagejpeg($img_r, $out_r, 75);
        return imagejpeg($img_l, $out_l, $quality) && imagejpeg($img_r, $out_r, $quality);
    }
}