<?php

/**
 * @author 继续
 */
class IndexController extends \Phalcon\Mvc\Controller
{

    /**
     */
    public function indexAction()
    {}

    public function route404Action()
    {
        $this->response->setStatusCode(404, 'Not Found');
    }

    public function authAction()
    {
        $this->response->setHeader('WWW-Authenticate', 'Basic realm="Auth for Solidarity"')->setStatusCode(401, 'Unauthorized');
    }
}