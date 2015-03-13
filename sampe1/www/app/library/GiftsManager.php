<?php

class GiftsManager extends \Phalcon\Mvc\User\Component
{

    const E_FILE_NOT_FOUND = 0;

    const E_OPEN_FAILED = 1;

    const E_READ_FAILED = 2;

    const SSCANF_FORMAT = 'gift0_%02d%1d%d%s';

    protected $config = null;

    protected $dlcPath = null;

    protected $fullPack = null;

    protected $configFile = null;

    protected $configLast = null;

    protected $data = null;

    protected $version = 0;

    protected $inited = false;

    protected $url = '';

    protected $backUrl = '';

    public function setDI($dependencyInjector)
    {
        parent::setDI($dependencyInjector);
        $config = $dependencyInjector->get('config')->gifts;
        $this->fullPack = $config->fullPack;
        $this->configFile = $config->configFile;
        $this->dlcPath = $config->dlcPath;
        $this->url = $config->url;
        $this->backUrl = $config->backUrl;
    }

    protected function initialize()
    {
        if ($this->inited) {
            return;
        }
        $ret = $this->readConfig();
        $config = $this->parseFullPack();
        if (is_array($config)) {
            // 保存
            $this->saveConfig($config);
            $ret = true;
            $this->data = $config;
            $this->version = $config['version'];
        }
        if ($ret) {
            $this->inited = true;
            return;
        }
        die(__FILE__ . ': Failed to initialize gifts data!');
    }

    protected function readConfig()
    {
        if (is_file($this->configFile)) {
            $this->data = include $this->configFile;
            $this->version = $this->data['version'];
            return true;
        }
        return false;
    }

    protected function parseFullPack()
    {
        if (is_file($this->fullPack)) {
            // 打开压缩包
            $root = zip_open($this->fullPack);
            if (! is_resource($root)) {
                return static::E_OPEN_FAILED;
            }
            $config = array(
                'data' => array()
            );
            $version = $this->version;
            while (true) {
                $entry = zip_read($root);
                if ($entry === false) {
                    break;
                }
                if (! is_resource($entry)) {
                    echo 'Read Failed';
                    return static::E_READ_FAILED;
                }
                $name = zip_entry_name($entry);
                list ($index, $discount, $price, $ext) = sscanf($name, static::SSCANF_FORMAT);
                if (is_null($index)) { // 不是要解析的文件名
                    continue;
                }
                $index = sprintf('%02d', $index);
                // 新配置
                $config['data'][$index] = array(
                    'index' => $index,
                    'discount' => $discount,
                    'price' => $price
                );
            }
            // 关闭压缩包
            zip_close($root);
            // 新文件名
            $filename = sprintf($this->dlcPath, $version);
            while (is_file($filename)) {
                ++ $version;
                $filename = sprintf($this->dlcPath, $version);
            }
            // 重命名包
            rename($this->fullPack, $filename);
            $config['version'] = $version;
            return $config;
        }
        return static::E_FILE_NOT_FOUND;
    }

    protected function saveConfig($config)
    {
        \Misc::cacheToFile($config, $this->configFile);
        /*
         * file_put_contents($this->configFile, array(
         * '<?php',
         * PHP_EOL,
         * 'return ',
         * var_export($config, true),
         * ';',
         * PHP_EOL
         * ));
         */
    }

    public function getData()
    {
        $this->initialize();
        return $this->data;
    }

    public function getVersion()
    {
        $this->initialize();
        return $this->version;
    }

    public function getUrl()
    {
        return array(
            sprintf($this->url, $this->getVersion()),
            sprintf($this->backUrl, $this->getVersion())
        );
    }

    public function getPack($version)
    {
        $file = sprintf($this->dlcPath, $version);
        if (is_file($file)) {
            return file_get_contents($file);
        }
        return false;
    }
}