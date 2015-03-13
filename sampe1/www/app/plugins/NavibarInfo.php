<?php

class NavibarInfo extends Phalcon\Mvc\User\Plugin
{

    protected $controllerName = NULL;

    protected $actionName = NULL;

    public function beforeExecuteRoute(\Phalcon\Events\Event $event, \Phalcon\Mvc\Dispatcher $dispacher, $data)
    {
        $this->controllerName = $dispacher->getPreviousControllerName() ?  : $dispacher->getControllerName();
        $this->actionName = $dispacher->getPreviousActionName() ?  : $dispacher->getActionName();
    }

    public function isController($controller, $caseSensive = false)
    {
        return ($caseSensive ? strcmp($controller, $this->controllerName) : strcasecmp($controller, $this->controllerName)) == 0;
    }

    public function isAction($controller, $action, $caseSensive = false)
    {
        return $this->isController($controller, $caseSensive) ? (($caseSensive ? strcmp($action, $this->actionName) : strcasecmp($action, $this->actionName)) == 0) : false;
    }
}