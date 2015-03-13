<?php

class BankController extends Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        if ($this->request->isPost() && $this->security->checkToken()) {
            $items = $this->request->getPost('items', null, array());
            $config = array();
            foreach ($items as $index => $item) {
                $item_config = array();
                $count = count($item['sale']);
                for ($sale_index = 0; $sale_index < $count; ++ $sale_index) {
                    $sale = $item['sale'][$sale_index];
                    $iapid = $item['iapid'][$sale_index];
                    $price = $item['price'][$sale_index];
                    $type = $item['type'][$sale_index];
                    $amount = $item['amount'][$sale_index];
                    $item_config = array(
                        'iapid' => $iapid,
                        'sale' => $sale,
                        'price' => $price,
                        'type' => $type,
                        'amount' => $amount
                    );
                    $config[$index][$sale] = $item_config;
                }
            }
            $this->showBoxManager->setConfig($config);
            $selectors = $this->request->get('selectors', null, array(
                1,
                1,
                1,
                1,
                1,
                1,
                1
            ));
            $this->showBoxManager->setSelectors($selectors);
            return $this->response->redirect('bank');
        }
        // $this->view->disable();
        // var_export($this->showBoxManager->getConfig());
        $this->view->setVar('config', $this->showBoxManager->getConfig());
        $this->view->setVar('selectors', $this->showBoxManager->getSelectors());
    }
}