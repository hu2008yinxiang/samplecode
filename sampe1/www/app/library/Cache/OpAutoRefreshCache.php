<?php
namespace Cache
{

    class OpAutoRefreshCache extends \Phalcon\Cache\Multiple
    {

        protected $opcacheEnabled = false;

        public function __construct($backends = null)
        {
            $this->opcacheEnabled = false;
            // 判定opcache是否启用
            // 如果opcache未启用 则文件缓存永远失败
            if (function_exists('opcache_get_configuration')) {
                $conf = opcache_get_configuration();
                $this->opcacheEnabled = $conf['directives']['opcache.enable'] && (php_sapi_name() == 'cli' ? $conf['directives']['opcache.enable_cli'] : true);
                unset($conf);
            }
            parent::__construct($backends);
        }

        public function exists($keyName = null, $lifetime = null)
        {
            // 如果是文件
            if (is_file($keyName)) {
                $path = realpath($keyName);
                if (! $this->opcacheEnabled || ! $path)
                    return false;
                $scripts = opcache_get_status();
                $scripts = $scripts['scripts'];
                if (! isset($scripts[$path])) { // 文件不在缓存里
                    return false;
                }
                $info = $scripts[$path];
                unset($scripts);
                $timestamp = parent::get($keyName . '/timestamp');
                if ($timestamp != $info['timestamp']) // 修改时间改变
                    return false;
            }
            return parent::exists($keyName, $lifetime);
        }

        public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = null)
        {
            parent::save($keyName, $content, $lifetime, $stopBuffer);
            if (is_file($keyName)) {
                $path = realpath($keyName);
                if (! $this->opcacheEnabled || ! $path) {
                    return;
                }
                $scripts = opcache_get_status();
                $scripts = $scripts['scripts'];
                if (! isset($scripts[$path])) { // 文件不在缓存里
                    return;
                }
                $info = $scripts[$path];
                unset($scripts);
                parent::save($keyName . '/timestamp', $info['timestamp'], $lifetime, $stopBuffer);
            }
        }

        public function get($keyName, $lifetime = null)
        {
            return parent::get($keyName, $lifetime);
        }
    }
}