<?php
namespace Cache
{

    class FileAutoRefreshCache extends \Phalcon\Cache\Multiple
    {

        public function exists($keyName = null, $lifetime = null)
        {
            if (is_file($keyName)) {
                $mtime = stat($keyName);
                $mtime = $mtime['mtime'];
                if ($mtime != parent::get($keyName . '/timestamp'))
                    return false;
            }
            return parent::exists($keyName, $lifetime);
        }

        public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = null)
        {
            parent::save($keyName, $content, $lifetime, $stopBuffer);
            if (is_file($keyName)) {
                $mtime = stat($keyName);
                $mtime = $mtime['mtime'];
                parent::save($keyName . '/timestamp', $mtime, $lifetime, $stopBuffer);
            }
        }
    }
}