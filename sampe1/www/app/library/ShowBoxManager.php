<?php

class ShowBoxManager extends Phalcon\Mvc\User\Component
{

    const CONFIG_FILE = 'showbox_config.php';

    protected $config = null;

    protected $selectors = null;

    public function setDI($di)
    {
        parent::setDI($di);
        $this->config = $this->loadConfig();
        $selectors = include DATA_PATH . '/showBoxSelectors.php';
        if (is_array($selectors)) {
            $this->selectors = $selectors;
        } else {
            $this->selectors = array(
                1,
                1,
                1,
                1,
                1,
                1,
                1
            );
        }
    }

    public function setConfig(array $config)
    {
        $file = DATA_PATH . '/' . static::CONFIG_FILE;
        \Misc::cacheToFile($config, $file);
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function loadConfig()
    {
        $file = DATA_PATH . '/' . static::CONFIG_FILE;
        $ret = include $file;
        if (! is_array($ret)) {
            $ret = array();
        }
        $config = array_merge($ret, array(
            0 => array(
                '1' => array(
                    'iapid' => '--',
                    'price' => 0,
                    'sale' => 1,
                    'amount' => 0,
                    'type' => 'chip'
                )
            ),
            1 => array(
                '1' => array(
                    'iapid' => '--',
                    'price' => 0,
                    'sale' => 1,
                    'amount' => 0,
                    'type' => 'chip'
                )
            ),
            2 => array(
                '1' => array(
                    'iapid' => '--',
                    'price' => 0,
                    'sale' => 1,
                    'amount' => 0,
                    'type' => 'chip'
                )
            ),
            3 => array(
                '1' => array(
                    'iapid' => '--',
                    'price' => 0,
                    'sale' => 1,
                    'amount' => 0,
                    'type' => 'chip'
                )
            ),
            4 => array(
                '1' => array(
                    'iapid' => '--',
                    'price' => 0,
                    'sale' => 1,
                    'amount' => 0,
                    'type' => 'chip'
                )
            ),
            5 => array(
                '1' => array(
                    'iapid' => '--',
                    'price' => 0,
                    'sale' => 1,
                    'amount' => 0,
                    'type' => 'chip'
                )
            ),
            6 => array(
                '1' => array(
                    'iapid' => '--',
                    'price' => 0,
                    'sale' => 1,
                    'amount' => 0,
                    'type' => 'chip'
                )
            )
        ));
        return $config;
    }

    public function getItemList()
    {
        $list = array();
        foreach ($this->config as $ci) {
            foreach ($ci as $item) {
                $list[] = $item;
            }
        }
        return $list;
    }

    public function getSelectors()
    {
        return $this->selectors;
    }

    public function setSelectors(array $selectors)
    {
        Misc::cacheToFile($selectors, DATA_PATH . '/showBoxSelectors.php');
        $this->selectors = $selectors;
    }

    public function getShowBox()
    {
        $selectors = $this->newsManager->getShowBoxSelectors();
        if ($selectors == null) {
            $selectors = $this->selectors;
        }
        $items = array();
        foreach ($selectors as $i => $s) {
            if (isset($this->config[$i][$s])) {
                $items[] = $this->config[$i][$s];
            } else {
                $items[] = $this->config[$i]['1'];
            }
        }
        return $items;
    }
}