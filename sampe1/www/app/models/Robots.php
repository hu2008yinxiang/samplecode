<?php
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Db\Column;

class Robots extends \Phalcon\Mvc\Model
{

    public function afterFetch()
    {
        
    }

    public function beforeSave()
    {
       
    }

    public function save($data = null, $whiteList = null)
    {
        parent::save($data, $whiteList);
        $this->afterFetch();
    }
}