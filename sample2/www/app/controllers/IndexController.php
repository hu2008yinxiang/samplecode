<?php

class IndexController extends \Phalcon\Mvc\Controller {
    
    public function indexAction()
    {}

    public function route404Action()
    {
        $this->response->setStatusCode(404, 'Not Found');
    }

    public function authAction()
    {
        if(defined('NO_PAGE')){
            $this->route404Action();
            return;
        }
        $this->response->setHeader('WWW-Authenticate', 'Basic realm="Crash Report"')->setStatusCode(401, 'Unauthorized');
        
    }
}