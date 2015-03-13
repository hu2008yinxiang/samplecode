<?php

class MinaController extends Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        $v = $this->view;
        $servers = array();
        if ($this->request->isPost() && $this->security->checkToken()) {
            $data = array();
            $index = 0;
            $hosts = $this->request->getPost('host', null, array());
            $ports = $this->request->getPost('port', null, array());
            $actives = $this->request->getPost('active', null, array());
            $error = false;
            $count = count($hosts);
            for ($index = 0; $index < $count; ++ $index) {
                $srv = array(
                    'host' => $hosts[$index],
                    'port' => $ports[$index],
                    'active' => $actives[$index] == 'true' ? 'true' : 'false',
                    'error' => false
                );
                if (empty($hosts[$index]) || empty($ports[$index])) {
                    $srv['error'] = true;
                    $error = $error || true;
                }
                $data[] = $srv;
            }
            if ($error) {
                $this->flash->error('Your inputs contains some errors, check and try it again.');
                $servers = $data;
            } else {
                $srvs = array(
                    'active' => array(),
                    'deactive' => array()
                );
                foreach ($data as $srv) {
                    if ($srv['active'] == 'true') {
                        $srvs['active'][] = array(
                            'host' => $srv['host'],
                            'port' => $srv['port']
                        );
                    } else {
                        $srvs['deactive'][] = array(
                            'host' => $srv['host'],
                            'port' => $srv['port']
                        );
                    }
                }
                $this->minaSwitcher->setMinaServers($srvs);
                return $this->response->redirect('mina');
            }
        }
        if (empty($servers)) {
            $minaSevers = $this->minaSwitcher->getMinaServers(false);
            $servers = array();
            foreach ($minaSevers as $status => $srvs) {
                foreach ($srvs as $srv) {
                    $servers[] = array(
                        'host' => $srv['host'],
                        'port' => $srv['port'],
                        'active' => $status == 'active' ? 'true' : 'false',
                        'error' => false
                    );
                }
            }
        }
        $d = $this->minaSwitcher->getMinaServers(true);
        $v->setVar('default_srv', $d[0]);
        $v->setVar('servers', $servers);
    }
}