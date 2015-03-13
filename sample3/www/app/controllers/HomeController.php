<?php

class HomeController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        // $this->view->pick('home/index');
        $this->view->setVar('title', 'TTTTT');
    }
}