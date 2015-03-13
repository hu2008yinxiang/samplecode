<?php

class ShopManager extends Phalcon\Mvc\User\Component
{

    protected $shopConfig = array();

    protected $dataFile = null;

    protected $bakFile = null;

    public function setDI($di)
    {
        parent::setDI($di);
        $this->dataFile = $di->get('config')->shopConfig->dataFile;
        $pathInfo = pathinfo($this->dataFile);
        $this->bakFile = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_bak.' . $pathInfo['extension'];
        if (is_file($this->dataFile)) {
            $this->shopConfig = include $this->dataFile;
            return;
        }
        if (is_file($this->bakFile)) {
            $this->shopConfig = include $this->bakFile;
        }
    }

    public function getShopConfig()
    {
        return $this->shopConfig;
    }

    public function hasSale()
    {
        foreach ($this->shopConfig as $config) {
            if ($config['sale'] > 1)
                return 1;
        }
        return 0;
    }

    public function saveConfig($data)
    {
        if (is_file($this->dataFile)) {
            rename($this->dataFile, $this->bakFile);
        }
        /*
         * file_put_contents($this->dataFile, array(
         * '<?php ',
         * PHP_EOL,
         * 'return unserialize(',
         * var_export(serialize($data), true),
         * ');',
         * PHP_EOL
         * ));
         */
        \Misc::cacheToFile($data, $this->dataFile);
        $this->shopConfig = $data;
    }

    public function getMonthlyOffer()
    {
        return $this->config->shopConfig->monthlyOffer->toArray();
    }

    public function hasMonthlyOffer($who)
    {
        $extra = $this->loadMonthlyOffer($who);
        return $extra->value['perday'] > 0;
    }

    public function loadMonthlyOffer($who)
    {
        return \Extras::load($who, \Extras::MONTHLY_OFFER, array(
            'end' => date_create('yesterday'),
            'current' => date_create('today'),
            'perday' => 0
        ));
    }
}