<?php

class MvcController extends Phalcon\Mvc\Controller
{

    protected function form($name, $default, $register = true)
    {
        $value = $default;
        if ($this->request->isPost()) {
            $value = $this->request->getPost($name, null, $default);
        } else {
            $value = $this->request->get($name, null, $default);
        }
        if ($register) {
            $this->view->setVar($name, $value);
        }
        return $value;
    }
}