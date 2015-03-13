<?php

class PhotoManager extends \Phalcon\Mvc\User\Component
{

    protected $savePath;

    protected $cachePath;

    /**
     *
     * @var DateInterval
     */
    protected $expire;

    protected $config;

    protected $default;

    const PHOTO_SIZE = 86;

    public function setDI($di)
    {
        parent::setDI($di);
        $config = $di->get('config')->photo;
        $this->savePath = $config->save_path;
        if (! file_exists($this->savePath)) {
            mkdir($this->savePath, 0777, true);
        }
        chmod($this->savePath, 0777);
        $this->savePath = realpath($this->savePath);
        $this->cachePath = $this->savePath . '/cache';
        if (! file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
        $this->expire = $config->expire;
        $this->config = $config;
        $this->default = $config->default;
    }

    public function download($url, $id)
    {
        $file_name = $this->genFilename($id);
        if (! $this->needRefresh($file_name))
            return true;
        
        $ret = @file_get_contents($url);
        if ($ret === false) {
            return false;
        }
        $img_src = imagecreatefromstring($ret);
        // $info = getimagesizefromstring($ret);
        // $src_w = $info[0];
        // $src_h = $info[1];
        // $src_type = $info[2];
        $src_h = imagesy($img_src);
        $src_w = imagesx($img_src);
        $size = min($src_w, $src_h);
        $offset_w = intval(($src_w - $size) / 2);
        $offset_h = intval(($src_h - $size) / 2);
        $croped = imagecreatetruecolor($size, $size);
        // imagejpeg ( $img_src, $file_name . '.org.jpg', 75 );
        $ret = imagecopy($croped, $img_src, 0, 0, $offset_w, $offset_h, $size, $size);
        // imagejpeg ( $$img_src, $file_name . '.org.jpg', 75 );
        imagedestroy($img_src);
        if ($ret) {
            $ret = imagejpeg($croped, $file_name, 75);
            imagedestroy($croped);
        }
        return $ret;
    }

    public function needRefresh($file_name)
    {
        if (strpos('x' . $file_name, $this->savePath) != 1) {
            return false;
        }
        $date_now = new DateTime();
        $date_now->sub($this->expire);
        $timestamp = $date_now->getTimestamp();
        
        $file = $file_name;
        $mtime = @filemtime($file);
        if ($mtime < $timestamp) {
            return true;
        }
        return false;
    }

    public function readPhoto($id)
    {
        $file_name = $this->getFilename($id);
        if (is_file($file_name))
            return file_get_contents($file_name);
        return false;
    }

    protected function getFilename($id)
    {
        $format = '%s/%s.jpg';
        $path = sprintf($format, $this->savePath, $id);
        if (is_file($path)) {
            $path = realpath($path);
            if (strpos('x' . $path, $this->savePath) == 1)
                return $path;
        }
        if (\FakeUserInfoContainer::isFakeUser($id)) {
            /*
            $cacheFile = sprintf($format, $this->cachePath, $id);
            if (is_file($cacheFile)) {
                $cacheFile = realpath($cacheFile);
                $ftime = @filemtime($cacheFile);
                $expire = 86400;
                $ftime += $expire;
                if ($ftime > time()) {
                    return $cacheFile;
                }
            }
            */
            $file = $this->fakeUserInfoContainer->getPhotoFile($id);
            if (is_file($file)) {
                //copy($file, $cacheFile);
                return $file;
            }
        }
        return $this->default;
    }

    protected function genFilename($id)
    {
        $format = '%s/%s.jpg';
        $path = sprintf($format, $this->savePath, $id);
        $path = \Utils::get_absolute_path($path);
        return $path;
    }
}