<?php

class MaintainceController extends Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        $msg = '';
        $file = DATA_PATH . '/maintaince.txt';
        if ($this->request->isPost() && $this->security->checkToken()) {
            $msg = $this->request->getPost('msg');
            $action = $this->request->getPost('action');
            if ($action == 'end') {
                unlink($file);
            } elseif ($action == 'start' && ! empty($msg)) {
                file_put_contents($file, $msg);
            }
            return $this->response->redirect('maintaince');
        }
        if (is_file($file)) {
            $msg = file_get_contents($file);
        }
        $this->view->setVar('msg', $msg);
    }
}