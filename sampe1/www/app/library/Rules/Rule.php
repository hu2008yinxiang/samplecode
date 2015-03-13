<?php
namespace Rules;

abstract class Rule extends \Phalcon\Mvc\User\Component
{

    protected $data = array();

    /**
     *
     * @return string
     */
    abstract public function getCatalog();

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}