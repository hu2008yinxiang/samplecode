<?php
namespace Flash\Adapter;

class Memory extends \Phalcon\Flash\Direct
{

    protected $msgs = array();

    public function message($type, $message)
    {
        $this->msgs[$type][] = $message;
    }

    public function output($remove = true)
    {
        foreach ($this->msgs as $type => $pack) {
            foreach ($pack as $p) {
                parent::message($type, $p);
            }
        }
        if ($remove)
            $this->clear();
    }

    public function has($type = null)
    {
        if (is_null($type)) {
            return ! empty($this->msgs);
        }
        return ! empty($this->msgs[$type]);
    }

    public function clear($type = null)
    {
        if (is_null($type)) {
            $this->msgs = array();
        } else {
            $this->msgs[$type] = array();
        }
    }
}