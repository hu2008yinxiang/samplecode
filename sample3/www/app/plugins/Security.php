<?php

class Security extends \Phalcon\Mvc\User\Plugin
{

    public function beforeExecuteRoute(\Phalcon\Events\Event $event, \Phalcon\Mvc\Dispatcher $dispacher, $data)
    {
        $auth = 'Guests';
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $user = $_SERVER['PHP_AUTH_USER'];
            $pwd = $_SERVER['PHP_AUTH_PW'];
            $auths = $this->config->auth->toArray();
            if (isset($auths[$user])) {
                if ($auths[$user]['pwd'] === $pwd) {
                    $auth = $auths[$user]['role'];
                }
            }
        }
        
        $controller = $dispacher->getControllerName();
        $action = $dispacher->getActionName();
        $acl = $this->acl;
        $allowed = $acl->isAllowed($auth, $controller, $action);
        //error_log($auth . '-' . $controller . '-' . $action . '-' . $allowed);
        if ($allowed != \Phalcon\Acl::ALLOW) {
            $dispacher->forward(array(
                'controller' => 'index',
                'action' => 'auth'
            ));
            return false;
        }
    }
}