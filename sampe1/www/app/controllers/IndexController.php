<?php

/**
 * @author 继续
 */
class IndexController extends MvcController
{

    /**
     */
    public function indexAction()
    {}

    public function route404Action()
    {
        $this->response->setStatusCode(404, 'Not Found');
    }

    public function logoutAction()
    {
        $this->session->remove('PHP_AUTH_USER');
        // $this->response->setStatusCode(401,'Unauthorized');
        return $this->response->redirect($this->request->getHTTPReferer());
    }

    public function authAction()
    {
        // $this->response->setHeader('WWW-Authenticate', 'Basic realm="Auth for Texas Holdem"')->setStatusCode(401, 'Unauthorized');
        // $request = $this->request;
        $username = $this->form('username', '');
        $password = $this->form('password', '');
        // $this->view->setVar('username', $username);
        // $this->view->setVar('password', $password);
        if ($this->request->isPost()) {
            if ($this->security->checkToken()) {
                $auths = $this->config->auth;
                if (array_key_exists($username, $auths)) {
                    if ($auths[$username]['pwd'] === $password) {
                        $this->session->set('PHP_AUTH_USER', $username);
                        $redirect = $this->request->get('redirect', null, $this->request->getHTTPReferer());
                        return $this->response->redirect($redirect);
                    }
                }
            }
            $this->flash->error('Login Failed!');
        }
        if ($this->session->get('PHP_AUTH_USER', false)) {
            $redirect = $this->request->getHTTPReferer();
            $self = $this->url->get('index/auth');
            if (stripos('x' . $redirect, $self) == 1) {
                $redirect = '';
            }
            return $this->response->redirect($redirect);
        }
    }
}