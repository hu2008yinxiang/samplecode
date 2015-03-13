<?php

/**
 * @author 继续
 */
class ShopController extends MvcController
{

    /**
     */
    public function indexAction()
    {
        $data = null;
        if ($this->request->isPost() && $this->security->checkToken()) {
            $data = array();
            $action = $this->request->get('action', null, '');
            switch ($action) {
                case 'save':
                    $indexs = $this->request->getPost('index', null, array());
                    $iapids = $this->request->getPost('iapid', null, array());
                    $prices = $this->request->getPost('price', null, array());
                    $amounts = $this->request->getPost('amount', null, array());
                    $sales = $this->request->getPost('sale', null, array());
                    foreach ($indexs as $i => $index) {
                        $iapid = $iapids[$i];
                        $price = $prices[$i];
                        $amount = $amounts[$i];
                        $sale = $sales[$i];
                        $data[$i] = array(
                            'index' => $index,
                            'iapid' => $iapid,
                            'price' => $price,
                            'amount' => $amount,
                            'sale' => $sale
                        );
                        
                        if ((empty($index) && $index != 0) || empty($iapid) || empty($price) || empty($amount) || empty($sale)) {
                            $this->flash->error('Row ' . ($i + 1) . ' has illegal input, please check again.');
                            $data[$i]['error'] = true;
                            continue;
                        }
                    }
                    if (! $this->flash->has('error')) {
                        $this->shopManager->saveConfig($data);
                        return $this->response->redirect('shop');
                    }
                    break;
            }
        }
        if ($this->request->isGet()) {
            $data = $this->shopManager->getShopConfig();
        }
        $this->view->setVar('data', $data);
    }
}